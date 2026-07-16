<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicFaqController extends Controller
{
    public function index(Request $request): View
    {
        $faqs = Faq::where('status', 'published')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('question', 'like', "%{$keyword}%")
                        ->orWhere('category', 'like', "%{$keyword}%")
                        ->orWhere('answer', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('category')
            ->latest()
            ->get();

        return view('public.faqs.index', [
            'faqs' => $faqs->groupBy('category'),
        ]);
    }
}
