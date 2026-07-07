<?php

namespace App\Http\Controllers;

use App\Models\SatisfactionSurvey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SatisfactionSurveyController extends Controller
{
    public function create(Request $request): View
    {
        return view('public.surveys.create', [
            'alreadySubmitted' => SatisfactionSurvey::where('respondent_key', $this->respondentKey($request))->exists(),
            'respondentTypes' => $this->respondentTypes(),
            'features' => $this->features(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $respondentKey = $this->respondentKey($request);

        if (SatisfactionSurvey::where('respondent_key', $respondentKey)->exists()) {
            return back()->with('error', 'Survei untuk periode bulan ini sudah pernah dikirim.');
        }

        $validated = $request->validate([
            'respondent_type' => ['required', Rule::in(array_keys($this->respondentTypes()))],
            'accessibility_rating' => ['required', 'integer', 'between:1,5'],
            'speed_rating' => ['required', 'integer', 'between:1,5'],
            'content_rating' => ['required', 'integer', 'between:1,5'],
            'ease_rating' => ['required', 'integer', 'between:1,5'],
            'overall_rating' => ['required', 'integer', 'between:1,5'],
            'found_document' => ['required', 'boolean'],
            'search_duration_minutes' => ['nullable', 'numeric', 'min:0.1', 'max:120'],
            'most_useful_feature' => ['required', Rule::in(array_keys($this->features()))],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        SatisfactionSurvey::create([
            'user_id' => $request->user()?->id,
            'respondent_key' => $respondentKey,
            'respondent_type' => $validated['respondent_type'],
            'accessibility_rating' => $validated['accessibility_rating'],
            'speed_rating' => $validated['speed_rating'],
            'content_rating' => $validated['content_rating'],
            'ease_rating' => $validated['ease_rating'],
            'overall_rating' => $validated['overall_rating'],
            'found_document' => $validated['found_document'],
            'search_duration_seconds' => filled($validated['search_duration_minutes'] ?? null)
                ? (int) round($validated['search_duration_minutes'] * 60)
                : null,
            'most_useful_feature' => $validated['most_useful_feature'],
            'feedback' => $validated['feedback'] ?? null,
            'ip_hash' => $request->ip() ? hash('sha256', config('app.key').'|'.$request->ip()) : null,
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);

        return redirect()
            ->route('surveys.create')
            ->with('success', 'Terima kasih. Penilaian Anda sudah tercatat untuk evaluasi PUSAKA HUKUM.');
    }

    private function respondentKey(Request $request): string
    {
        if ($request->user()) {
            $identity = 'user:'.$request->user()->id;
        } else {
            $identity = $request->session()->get('survey_identity');

            if (! $identity) {
                $identity = (string) Str::uuid();
                $request->session()->put('survey_identity', $identity);
            }

            $identity = 'guest:'.$identity;
        }

        return hash('sha256', $identity.'|'.now()->format('Y-m'));
    }

    private function respondentTypes(): array
    {
        return [
            'personel_polri' => 'Personel Polri',
            'masyarakat' => 'Masyarakat',
            'akademisi' => 'Akademisi/Mahasiswa',
            'praktisi_hukum' => 'Praktisi Hukum',
            'lainnya' => 'Lainnya',
        ];
    }

    private function features(): array
    {
        return [
            'pencarian' => 'Pencarian dokumen',
            'dokumen' => 'Bank dokumen hukum',
            'artikel' => 'Artikel edukasi',
            'faq' => 'FAQ hukum',
            'konsultasi' => 'Konsultasi informasi hukum',
        ];
    }
}
