<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\View\View;

class PublicArticleController extends Controller
{
    public function index(): View
    {
        return view('public.articles.index', [
            'articles' => Article::where('status', 'published')->latest('published_at')->paginate(9),
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->status === 'published', 404);

        return view('public.articles.show', [
            'article' => $article->load('author'),
        ]);
    }
}
