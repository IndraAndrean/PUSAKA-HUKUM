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
            'email' => ['nullable', 'email:filter', 'max:150'],
            'question' => ['required', 'string', 'min:10'],
        ]);

        $consultation = Consultation::create([
            ...$data,
            'user_id' => $request->user()?->id,
        ]);

        return back()
            ->with('success', 'Pertanyaan konsultasi berhasil dikirim.')
            ->with('tracking_code', $consultation->tracking_code);
    }

    public function status(Request $request): View
    {
        $validated = $request->validate([
            'tracking_code' => ['nullable', 'string', 'max:20'],
        ]);

        $trackingCode = filled($validated['tracking_code'] ?? null)
            ? strtoupper(trim($validated['tracking_code']))
            : null;

        return view('public.consultation-status', [
            'searched' => filled($trackingCode),
            'consultation' => $trackingCode
                ? Consultation::where('tracking_code', $trackingCode)->first()
                : null,
        ]);
    }

    public function mine(Request $request): View
    {
        return view('account.consultations', [
            'consultations' => Consultation::where('user_id', $request->user()->id)
                ->latest()
                ->paginate(10),
        ]);
    }
}
