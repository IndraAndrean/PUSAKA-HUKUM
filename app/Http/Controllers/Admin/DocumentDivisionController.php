<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentDivision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentDivisionController extends Controller
{
    public function index(Request $request): View
    {
        $documentDivisions = DocumentDivision::withCount('documents')
            ->when($request->filled('q'), fn ($query) => $query
                ->where('name', 'like', '%'.$request->string('q').'%')
                ->orWhere('code', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.document-divisions.index', compact('documentDivisions'));
    }

    public function create(): View
    {
        return view('admin.document-divisions.form', ['documentDivision' => new DocumentDivision]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        DocumentDivision::create($data);

        return redirect()->route('admin.document-divisions.index')->with('success', 'Bidang/Subbidang berhasil ditambahkan.');
    }

    public function edit(DocumentDivision $documentDivision): View
    {
        return view('admin.document-divisions.form', compact('documentDivision'));
    }

    public function update(Request $request, DocumentDivision $documentDivision): RedirectResponse
    {
        $data = $this->validated($request, $documentDivision);

        if ($documentDivision->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $documentDivision);
        }

        $documentDivision->update($data);

        return redirect()->route('admin.document-divisions.index')->with('success', 'Bidang/Subbidang berhasil diperbarui.');
    }

    public function destroy(DocumentDivision $documentDivision): RedirectResponse
    {
        if ($documentDivision->documents()->exists()) {
            return back()->with('error', 'Bidang/Subbidang masih digunakan dan tidak dapat dihapus.');
        }

        $documentDivision->delete();

        return back()->with('success', 'Bidang/Subbidang berhasil dihapus.');
    }

    private function validated(Request $request, ?DocumentDivision $documentDivision = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('document_divisions')->ignore($documentDivision)],
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique('document_divisions')->ignore($documentDivision),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function uniqueSlug(string $name, ?DocumentDivision $ignored = null): string
    {
        $base = Str::slug($name) ?: 'bidang-subbidang';
        $slug = $base;
        $counter = 2;

        while (DocumentDivision::where('slug', $slug)
            ->when($ignored, fn ($query) => $query->whereKeyNot($ignored->id))
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
