<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentAccessLog;
use App\Models\DocumentDownloadLog;
use App\Models\DocumentType;
use App\Models\LegalCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $allCollections = $request->input('collection') === 'all';

        $documents = $this->filteredQuery($request, $allCollections)
            ->with(['type', 'category', 'division'])
            ->tap(fn (Builder $query) => $this->applySort($query, $request))
            ->paginate(10)
            ->withQueryString();

        $typesBase = $allCollections ? DocumentType::query() : DocumentType::where('collection', 'produk_hukum');
        $types = $typesBase->orderBy('name')->get()->map(function (DocumentType $type) use ($request, $allCollections) {
            $type->facet_count = $this->filteredQuery($request, $allCollections, ['type'])
                ->where('document_type_id', $type->id)->count();

            return $type;
        });

        $categories = LegalCategory::orderBy('name')->get()->map(function (LegalCategory $category) use ($request, $allCollections) {
            $category->facet_count = $this->filteredQuery($request, $allCollections, ['category'])
                ->where('legal_category_id', $category->id)->count();

            return $category;
        });

        $statusFacets = collect(Document::STATUS_LABELS)->map(fn ($label, $value) => [
            'value' => $value,
            'label' => $label,
            'count' => $this->filteredQuery($request, $allCollections, ['status'])
                ->where('document_status', $value)->count(),
        ])->values();

        $years = Document::select('year')
            ->visibleFor($request->user())
            ->when(! $allCollections, fn ($query) => $query
                ->whereHas('type', fn ($type) => $type->where('collection', 'produk_hukum')))
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('public.documents.index', [
            'documents' => $documents,
            'types' => $types,
            'categories' => $categories,
            'statusFacets' => $statusFacets,
            'years' => $years,
            'allCollections' => $allCollections,
            'sort' => $request->string('sort')->toString() ?: ($request->filled('q') ? 'relevansi' : 'terbaru'),
        ]);
    }

    private function filteredQuery(Request $request, bool $allCollections, array $except = []): Builder
    {
        $query = Document::query()->visibleFor($request->user());

        if (! $allCollections) {
            $query->whereHas('type', fn ($type) => $type->where('collection', 'produk_hukum'));
        }

        if ($request->filled('q')) {
            $keyword = $request->string('q')->toString();
            $query->search($keyword);
        }

        if (! in_array('type', $except, true) && $request->filled('type')) {
            $query->where('document_type_id', $request->integer('type'));
        }

        if (! in_array('category', $except, true) && $request->filled('category')) {
            $query->where('legal_category_id', $request->integer('category'));
        }

        if (! in_array('year', $except, true) && $request->filled('year')) {
            $query->where('year', $request->integer('year'));
        }

        if (! in_array('status', $except, true) && $request->filled('status')) {
            $query->where('document_status', $request->string('status'));
        }

        return $query;
    }

    private function applySort(Builder $query, Request $request): void
    {
        $sort = $request->string('sort')->toString() ?: ($request->filled('q') ? 'relevansi' : 'terbaru');

        match ($sort) {
            'terlama' => $query->oldest(),
            'populer' => $query->orderByDesc('downloads_count')->orderByDesc('views_count'),
            'judul' => $query->orderBy('title'),
            'relevansi' => $request->filled('q')
                ? $query->orderByRaw('CASE WHEN title LIKE ? THEN 0 ELSE 1 END', ['%'.$request->string('q').'%'])->latest()
                : $query->latest(),
            default => $query->latest(),
        };
    }

    public function show(Request $request, Document $document): View
    {
        $this->authorizeDocument($request, $document);

        $recentlyViewedFromThisIp = DocumentAccessLog::where('document_id', $document->id)
            ->where('ip_address', $request->ip())
            ->where('accessed_at', '>=', now()->subMinutes(30))
            ->exists();

        if (! $recentlyViewedFromThisIp) {
            Document::withoutEvents(fn () => $document->increment('views_count'));
        }

        DocumentAccessLog::create([
            'user_id' => $request->user()?->id,
            'document_id' => $document->id,
            'ip_address' => $request->ip(),
            'accessed_at' => now(),
        ]);

        $document->load(['type', 'category', 'division', 'uploader']);

        $relatedDocuments = Document::with(['type', 'category', 'division'])
            ->visibleFor($request->user())
            ->whereKeyNot($document->id)
            ->when($document->legal_category_id, fn ($query) => $query->where('legal_category_id', $document->legal_category_id))
            ->latest()
            ->take(4)
            ->get();

        return view('public.documents.show', [
            'document' => $document,
            'hasFile' => $this->fileExists($document),
            'relatedDocuments' => $relatedDocuments,
        ]);
    }

    public function download(Request $request, Document $document): BinaryFileResponse|RedirectResponse
    {
        $this->authorizeDocument($request, $document);

        if (! $this->fileExists($document)) {
            return back()->with('error', 'File dokumen belum tersedia.');
        }

        Document::withoutEvents(fn () => $document->increment('downloads_count'));
        DocumentDownloadLog::create([
            'user_id' => $request->user()?->id,
            'document_id' => $document->id,
            'ip_address' => $request->ip(),
            'downloaded_at' => now(),
        ]);

        return response()->download(
            Storage::disk('documents')->path($document->file_path),
            basename($document->file_path),
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
