<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Faq;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('public.home', [
            'totalDocuments' => Document::visibleFor(auth()->user())->count(),
            'totalPublicDocuments' => Document::where('access_level', 'publik')->count(),
            'totalTypes' => DocumentType::count(),
            'totalLibrary' => Document::visibleFor(auth()->user())
                ->whereHas('type', fn ($query) => $query->library())
                ->count(),
            'totalFaqs' => Faq::where('status', 'published')->count(),
            'documentTypes' => DocumentType::withCount(['documents' => fn ($query) => $query->visibleFor(auth()->user())])
                ->where('collection', 'produk_hukum')
                ->orderBy('name')
                ->get(),
            'libraryTypes' => DocumentType::library()
                ->withCount(['documents' => fn ($query) => $query->visibleFor(auth()->user())])
                ->orderBy('name')
                ->get(),
            'latestDocuments' => Document::with(['type', 'category'])
                ->visibleFor(auth()->user())
                ->latest()
                ->take(4)
                ->get(),
            'latestArticles' => Article::where('status', 'published')
                ->latest('published_at')
                ->take(3)
                ->get(),
        ]);
    }
}
