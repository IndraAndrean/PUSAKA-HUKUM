<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function create(): View
    {
        return view('public.consultation');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'question' => ['required', 'string', 'min:10'],
        ]);

        Consultation::create([
            ...$data,
            'user_id' => $request->user()?->id,
        ]);

        return back()->with('success', 'Pertanyaan konsultasi berhasil dikirim.');
    }
}
