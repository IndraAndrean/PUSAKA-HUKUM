<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function index(Request $request): View
    {
        $consultations = Consultation::with(['user', 'answerer'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('question', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.consultations.index', compact('consultations'));
    }

    public function show(Consultation $consultation): View
    {
        return view('admin.consultations.show', [
            'consultation' => $consultation->load(['user', 'answerer']),
        ]);
    }

    public function update(Request $request, Consultation $consultation): RedirectResponse
    {
        $data = $request->validate([
            'answer' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['masuk', 'diproses', 'dijawab', 'selesai'])],
        ]);

        if (filled($data['answer'])) {
            $data['answered_by'] = $request->user()->id;
            $data['answered_at'] = now();

            if ($data['status'] === 'masuk') {
                $data['status'] = 'dijawab';
            }
        }

        $consultation->update($data);

        return redirect()->route('admin.consultations.show', $consultation)
            ->with('success', 'Konsultasi berhasil diperbarui.');
    }

    public function destroy(Consultation $consultation): RedirectResponse
    {
        $consultation->delete();

        return redirect()->route('admin.consultations.index')->with('success', 'Konsultasi berhasil dihapus.');
    }
}
