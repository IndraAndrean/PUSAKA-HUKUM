<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\LegalCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicEducationMaterialController extends Controller
{
    public function __invoke(Request $request): View
    {
        $documents = Document::with(['type', 'category'])
            ->visibleFor($request->user())
            ->whereHas('type', fn ($query) => $query->education())
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();
                $query->search($keyword);
            })
            ->when($request->filled('category'), fn ($query) => $query->where('legal_category_id', $request->integer('category')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $visibleMaterials = Document::visibleFor($request->user())
            ->whereHas('type', fn ($query) => $query->education());

        return view('public.education-materials.index', [
            'documents' => $documents,
            'categories' => LegalCategory::whereHas('documents', fn ($query) => $query
                ->visibleFor($request->user())
                ->whereHas('type', fn ($type) => $type->education()))
                ->orderBy('name')
                ->get(),
            'totalMaterials' => (clone $visibleMaterials)->count(),
        ]);
    }
}
