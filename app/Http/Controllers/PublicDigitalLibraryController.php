<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\LegalCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicDigitalLibraryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $documents = Document::with(['type', 'category'])
            ->visibleFor($request->user())
            ->whereHas('type', fn ($query) => $query->library())
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('title', 'like', "%{$keyword}%")
                        ->orWhere('author', 'like', "%{$keyword}%")
                        ->orWhere('publisher', 'like', "%{$keyword}%")
                        ->orWhere('isbn_issn', 'like', "%{$keyword}%")
                        ->orWhere('keywords', 'like', "%{$keyword}%")
                        ->orWhere('summary', 'like', "%{$keyword}%")
                        ->orWhere('abstract', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('type'), fn ($query) => $query->where('document_type_id', $request->integer('type')))
            ->when($request->filled('category'), fn ($query) => $query->where('legal_category_id', $request->integer('category')))
            ->when($request->filled('year'), fn ($query) => $query->where('year', $request->integer('year')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $visibleLibrary = Document::visibleFor($request->user())
            ->whereHas('type', fn ($query) => $query->library());

        return view('public.library.index', [
            'documents' => $documents,
            'types' => DocumentType::library()->withCount([
                'documents' => fn ($query) => $query->visibleFor($request->user()),
            ])->orderBy('name')->get(),
            'categories' => LegalCategory::whereHas('documents', fn ($query) => $query
                ->visibleFor($request->user())
                ->whereHas('type', fn ($type) => $type->library()))
                ->orderBy('name')
                ->get(),
            'years' => (clone $visibleLibrary)->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year'),
            'totalLibrary' => (clone $visibleLibrary)->count(),
            'totalAuthors' => (clone $visibleLibrary)->whereNotNull('author')->distinct()->count('author'),
        ]);
    }
}
