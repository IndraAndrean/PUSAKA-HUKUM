<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\View\View;

class PublicFaqController extends Controller
{
    public function index(): View
    {
        return view('public.faqs.index', [
            'faqs' => Faq::where('status', 'published')->orderBy('category')->latest()->get(),
        ]);
    }
}
