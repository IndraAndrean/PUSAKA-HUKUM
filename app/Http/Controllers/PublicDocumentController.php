<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentAccessLog;
use App\Models\DocumentDownloadLog;
use App\Models\DocumentType;
use App\Models\LegalCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $documents = Document::with(['type', 'category'])
            ->visibleFor($request->user())
            ->when($request->input('collection') !== 'all', fn ($query) => $query
                ->whereHas('type', fn ($type) => $type->where('collection', 'produk_hukum')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('title', 'like', "%{$keyword}%")
                        ->orWhere('author', 'like', "%{$keyword}%")
                        ->orWhere('publisher', 'like', "%{$keyword}%")
                        ->orWhere('isbn_issn', 'like', "%{$keyword}%")
                        ->orWhere('document_number', 'like', "%{$keyword}%")
                        ->orWhere('year', 'like', "%{$keyword}%")
                        ->orWhere('keywords', 'like', "%{$keyword}%")
                        ->orWhere('summary', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('document_type_id', $request->integer('type')))
            ->when($request->filled('category'), fn ($query) => $query->where('legal_category_id', $request->integer('category')))
            ->when($request->filled('year'), fn ($query) => $query->where('year', $request->integer('year')))
            ->when($request->filled('status'), fn ($query) => $query->where('document_status', $request->string('status')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('public.documents.index', [
            'documents' => $documents,
            'types' => DocumentType::when(
                $request->input('collection') !== 'all',
                fn ($query) => $query->where('collection', 'produk_hukum')
            )->orderBy('name')->get(),
            'categories' => LegalCategory::orderBy('name')->get(),
            'years' => Document::select('year')
                ->when($request->input('collection') !== 'all', fn ($query) => $query
                    ->whereHas('type', fn ($type) => $type->where('collection', 'produk_hukum')))
                ->whereNotNull('year')
                ->distinct()
                ->orderByDesc('year')
                ->pluck('year'),
            'allCollections' => $request->input('collection') === 'all',
        ]);
    }

    public function show(Request $request, Document $document): View
    {
        $this->authorizeDocument($request, $document);

        $document->increment('views_count');
        DocumentAccessLog::create([
            'user_id' => $request->user()?->id,
            'document_id' => $document->id,
            'ip_address' => $request->ip(),
            'accessed_at' => now(),
        ]);

        return view('public.documents.show', [
            'document' => $document->load(['type', 'category', 'uploader']),
            'hasFile' => $this->fileExists($document),
        ]);
    }

    public function download(Request $request, Document $document): BinaryFileResponse|RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        if (! $this->fileExists($document)) {
            return back()->with('error', 'File dokumen belum tersedia.');
        }

        $document->increment('downloads_count');
        DocumentDownloadLog::create([
            'user_id' => $request->user()?->id,
            'document_id' => $document->id,
            'ip_address' => $request->ip(),
            'downloaded_at' => now(),
        ]);

        return response()->download(
            Storage::disk('documents')->path($document->file_path),
            str($document->title)->slug('-').'.pdf',
            [
                'Cache-Control' => 'private, no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function preview(Request $request, Document $document): BinaryFileResponse|RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        if (! $this->fileExists($document)) {
            return back()->with('error', 'File dokumen belum tersedia.');
        }

        $filename = str($document->title)->slug('-').'.pdf';

        return response()->file(
            Storage::disk('documents')->path($document->file_path),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
                'Cache-Control' => 'private, no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    private function authorizeDocument(Request $request, Document $document): void
    {
        $allowed = Document::visibleFor($request->user())->whereKey($document->id)->exists();

        abort_unless($allowed, 403, 'Dokumen ini tidak tersedia untuk akun Anda.');
    }

    private function fileExists(Document $document): bool
    {
        return filled($document->file_path)
            && Storage::disk('documents')->exists($document->file_path);
    }
}
