<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LegalCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $legalCategories = LegalCategory::withCount('documents')
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.legal-categories.index', compact('legalCategories'));
    }

    public function create(): View
    {
        return view('admin.legal-categories.form', ['legalCategory' => new LegalCategory()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        LegalCategory::create($data);

        return redirect()->route('admin.legal-categories.index')->with('success', 'Kategori hukum berhasil ditambahkan.');
    }

    public function edit(LegalCategory $legalCategory): View
    {
        return view('admin.legal-categories.form', compact('legalCategory'));
    }

    public function update(Request $request, LegalCategory $legalCategory): RedirectResponse
    {
        $data = $this->validated($request, $legalCategory);

        if ($legalCategory->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $legalCategory);
        }

        $legalCategory->update($data);

        return redirect()->route('admin.legal-categories.index')->with('success', 'Kategori hukum berhasil diperbarui.');
    }

    public function destroy(LegalCategory $legalCategory): RedirectResponse
    {
        if ($legalCategory->documents()->exists()) {
            return back()->with('error', 'Kategori hukum masih digunakan dan tidak dapat dihapus.');
        }

        $legalCategory->delete();

        return back()->with('success', 'Kategori hukum berhasil dihapus.');
    }

    private function validated(Request $request, ?LegalCategory $legalCategory = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('legal_categories')->ignore($legalCategory)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function uniqueSlug(string $name, ?LegalCategory $ignored = null): string
    {
        $base = Str::slug($name) ?: 'kategori-hukum';
        $slug = $base;
        $counter = 2;

        while (LegalCategory::where('slug', $slug)
            ->when($ignored, fn ($query) => $query->whereKeyNot($ignored->id))
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
