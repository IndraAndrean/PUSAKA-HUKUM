<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentTypeController extends Controller
{
    public function index(Request $request): View
    {
        $documentTypes = DocumentType::withCount('documents')
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.document-types.index', compact('documentTypes'));
    }

    public function create(): View
    {
        return view('admin.document-types.form', [
            'documentType' => new DocumentType,
            'collections' => DocumentType::COLLECTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        DocumentType::create($data);

        return redirect()->route('admin.document-types.index')->with('success', 'Jenis dokumen berhasil ditambahkan.');
    }

    public function edit(DocumentType $documentType): View
    {
        return view('admin.document-types.form', [
            'documentType' => $documentType,
            'collections' => DocumentType::COLLECTIONS,
        ]);
    }

    public function update(Request $request, DocumentType $documentType): RedirectResponse
    {
        $data = $this->validated($request, $documentType);

        if ($documentType->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $documentType);
        }

        $documentType->update($data);

        return redirect()->route('admin.document-types.index')->with('success', 'Jenis dokumen berhasil diperbarui.');
    }

    public function destroy(DocumentType $documentType): RedirectResponse
    {
        if ($documentType->documents()->exists()) {
            return back()->with('error', 'Jenis dokumen masih digunakan dan tidak dapat dihapus.');
        }

        $documentType->delete();

        return back()->with('success', 'Jenis dokumen berhasil dihapus.');
    }

    private function validated(Request $request, ?DocumentType $documentType = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('document_types')->ignore($documentType)],
            'code_prefix' => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Z0-9-]+$/',
                Rule::unique('document_types')->ignore($documentType),
            ],
            'review_interval_months' => ['required', 'integer', 'min:0', 'max:60'],
            'collection' => ['nullable', Rule::in(array_keys(DocumentType::COLLECTIONS))],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['collection'] ??= $documentType?->collection ?: 'produk_hukum';

        return $data;
    }

    private function uniqueSlug(string $name, ?DocumentType $ignored = null): string
    {
        $base = Str::slug($name) ?: 'jenis-dokumen';
        $slug = $base;
        $counter = 2;

        while (DocumentType::where('slug', $slug)
            ->when($ignored, fn ($query) => $query->whereKeyNot($ignored->id))
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
