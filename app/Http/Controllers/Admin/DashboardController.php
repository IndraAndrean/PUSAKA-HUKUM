<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Consultation;
use App\Models\Document;
use App\Models\DocumentAccessLog;
use App\Models\DocumentDownloadLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $allDocuments = Document::with('type')->get();
        $months = collect(range(5, 0))
            ->map(fn (int $monthsAgo) => now()->startOfMonth()->subMonths($monthsAgo))
            ->push(now()->startOfMonth());

        $monthlyStatistics = $months->map(function (Carbon $month) {
            $end = $month->copy()->endOfMonth();

            return [
                'label' => $month->translatedFormat('M Y'),
                'uploads' => Document::whereBetween('created_at', [$month, $end])->count(),
                'views' => DocumentAccessLog::whereBetween('accessed_at', [$month, $end])->count(),
                'downloads' => DocumentDownloadLog::whereBetween('downloaded_at', [$month, $end])->count(),
            ];
        });

        $currentMonth = $monthlyStatistics->last();
        $previousMonth = $monthlyStatistics->slice(-2, 1)->first();

        return view('admin.dashboard', [
            'documentsDelta' => $currentMonth['uploads'] - ($previousMonth['uploads'] ?? 0),
            'viewsDelta' => $currentMonth['views'] - ($previousMonth['views'] ?? 0),
            'downloadsDelta' => $currentMonth['downloads'] - ($previousMonth['downloads'] ?? 0),
            'totalDocuments' => Document::count(),
            'totalUsers' => User::count(),
            'totalArticles' => Article::count(),
            'totalConsultations' => Consultation::count(),
            'publicDocuments' => Document::where('access_level', 'publik')->count(),
            'internalDocuments' => Document::where('access_level', 'internal')->count(),
            'restrictedDocuments' => Document::where('access_level', 'terbatas')->count(),
            'popularDocuments' => Document::with('type')->orderByDesc('views_count')->take(5)->get(),
            'mostDownloadedDocuments' => Document::with('type')->orderByDesc('downloads_count')->take(5)->get(),
            'totalDownloads' => DocumentDownloadLog::count(),
            'totalViews' => DocumentAccessLog::count(),
            'openConsultations' => Consultation::whereIn('status', ['masuk', 'diproses'])->count(),
            'averageMetadataCompleteness' => (int) round($allDocuments->avg('metadata_completeness') ?? 0),
            'completeMetadataDocuments' => $allDocuments->where('metadata_completeness', '>=', 95)->count(),
            'incompleteMetadataDocuments' => $allDocuments->where('metadata_completeness', '<', 95)->count(),
            'documentsDueReview' => Document::whereNotNull('next_review_at')->whereDate('next_review_at', '<=', today())->count(),
            'documentsNeedingAttention' => $allDocuments
                ->filter(fn ($document) => $document->metadata_completeness < 95 || $document->needs_review)
                ->sortBy('metadata_completeness')
                ->take(6),
            'monthlyStatistics' => $monthlyStatistics,
            'latestAccesses' => DocumentAccessLog::with(['document', 'user'])
                ->latest('accessed_at')
                ->take(6)
                ->get(),
        ]);
    }
}
