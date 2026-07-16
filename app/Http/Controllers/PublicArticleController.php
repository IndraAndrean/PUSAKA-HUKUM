<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicArticleController extends Controller
{
    public function index(Request $request): View
    {
        $articles = Article::where('status', 'published')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('title', 'like', "%{$keyword}%")
                        ->orWhere('category', 'like', "%{$keyword}%")
                        ->orWhere('excerpt', 'like', "%{$keyword}%")
                        ->orWhere('content', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = Article::where('status', 'published')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('public.articles.index', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->status === 'published', 404);

        $article->load('author');

        $relatedArticles = Article::where('status', 'published')
            ->whereKeyNot($article->id)
            ->when($article->category, fn ($query) => $query->where('category', $article->category))
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('public.articles.show', [
            'article' => $article,
            'relatedArticles' => $relatedArticles,
        ]);
    }
}
