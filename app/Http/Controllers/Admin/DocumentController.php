<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\LegalCategory;
use App\Services\DocumentStandardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(Request $request): View
    {
        $documents = Document::with(['type', 'category', 'uploader'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = $request->string('q')->toString();
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('title', 'like', "%{$keyword}%")
                        ->orWhere('author', 'like', "%{$keyword}%")
                        ->orWhere('isbn_issn', 'like', "%{$keyword}%")
                        ->orWhere('document_code', 'like', "%{$keyword}%")
                        ->orWhere('document_number', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('collection'), fn ($query) => $query
                ->whereHas('type', fn ($type) => $type->where('collection', $request->string('collection')->toString())))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.documents.index', [
            'documents' => $documents,
            'collections' => DocumentType::COLLECTIONS,
        ]);
    }

    public function create(): View
    {
        return view('admin.documents.form', $this->formData(new Document));
    }

    public function store(Request $request, DocumentStandardService $standards): RedirectResponse
    {
        $data = $this->validated($request, null, true);
        $type = DocumentType::findOrFail($data['document_type_id']);
        $data['document_code'] = $standards->nextCode($type, (int) $data['year']);
        $data['uploaded_by'] = $request->user()->id;
        $data = $standards->applyReviewSchedule($data, $type);

        if ($request->hasFile('file')) {
            $data['file_path'] = $standards->storeUploadedPdf($request->file('file'), $data, $type);
        }

        unset($data['file']);

        Document::create($data);

        return redirect()->route('admin.documents.index')->with('success', 'Dokumen berhasil ditambahkan.');
    }

    public function edit(Document $document): View
    {
        return view('admin.documents.form', $this->formData($document));
    }

    public function update(
        Request $request,
        Document $document,
        DocumentStandardService $standards,
    ): RedirectResponse {
        $data = $this->validated($request, $document, false);
        $type = DocumentType::findOrFail($data['document_type_id']);

        if (
            (int) $document->document_type_id !== (int) $data['document_type_id']
            || (int) $document->year !== (int) $data['year']
        ) {
            $data['document_code'] = $standards->nextCode($type, (int) $data['year']);
        }

        $data = $standards->applyReviewSchedule($data, $type);

        if ($request->hasFile('file')) {
            $newPath = $standards->storeUploadedPdf(
                $request->file('file'),
                array_merge($document->toArray(), $data),
                $type,
            );
            $oldPath = $document->file_path;
            $data['file_path'] = $newPath;

            if ($oldPath) {
                Storage::disk('documents')->delete($oldPath);
                Storage::disk('public')->delete($oldPath);
            }
        }

        unset($data['file']);

        $document->update($data);

        return redirect()->route('admin.documents.index')->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        if ($document->file_path) {
            Storage::disk('documents')->delete($document->file_path);
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    private function validated(Request $request, ?Document $document, bool $requireFile): array
    {
        $type = DocumentType::find($request->integer('document_type_id'));
        $library = $type?->isLibrary() ?? false;

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'author' => [$library ? 'required' : 'nullable', 'string', 'max:255'],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'document_number' => [$library ? 'nullable' : 'required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'enacted_date' => [$library ? 'nullable' : 'required', 'date'],
            'effective_date' => [$library ? 'nullable' : 'required', 'date', 'after_or_equal:enacted_date'],
            'issuing_institution' => ['required', 'string', 'max:255'],
            'publisher' => [$library ? 'required' : 'nullable', 'string', 'max:255'],
            'isbn_issn' => ['nullable', 'string', 'max:50'],
            'edition_volume' => ['nullable', 'string', 'max:100'],
            'document_status' => ['required', 'in:berlaku,dicabut,diubah,tidak_berlaku'],
            'legal_category_id' => ['required', 'exists:legal_categories,id'],
            'bidang_subbidang' => ['required', 'in:kum,bankum,sunluhkum'],
            'keywords' => ['required', 'string'],
            'summary' => ['required', 'string', 'min:20'],
            'abstract' => ['nullable', 'string'],
            'legal_basis' => ['nullable', 'string'],
            'related_regulation' => ['nullable', 'string'],
            'document_version' => ['required', 'string', 'max:30'],
            'last_reviewed_at' => ['nullable', 'date'],
            'access_level' => ['required', 'in:publik,internal,terbatas'],
            'file' => [$requireFile ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:20480'],
        ], [
            'effective_date.after_or_equal' => 'Tanggal berlaku tidak boleh lebih awal dari tanggal penetapan.',
            'summary.min' => 'Ringkasan dokumen minimal 20 karakter.',
            'file.mimes' => 'File dokumen harus berformat PDF.',
            'file.max' => 'Ukuran file PDF maksimal 20 MB.',
        ]);

        $validator->after(function ($validator) use ($request, $document, $library) {
            $keywords = collect(preg_split('/[,;]+/', (string) $request->input('keywords')))
                ->map(fn ($keyword) => trim($keyword))
                ->filter()
                ->unique(fn ($keyword) => mb_strtolower($keyword));

            if ($keywords->count() < 3) {
                $validator->errors()->add('keywords', 'Masukkan minimal 3 kata kunci yang dipisahkan koma.');
            }

            if (
                $request->filled('document_type_id')
                && $request->filled('document_number')
                && $request->filled('year')
            ) {
                $duplicate = Document::query()
                    ->where('document_type_id', $request->integer('document_type_id'))
                    ->whereRaw('LOWER(document_number) = ?', [mb_strtolower(trim($request->string('document_number')->toString()))])
                    ->where('year', $request->integer('year'))
                    ->when($document, fn ($query) => $query->whereKeyNot($document->id))
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add(
                        'document_number',
                        'Dokumen dengan jenis, nomor, dan tahun yang sama sudah terdaftar.'
                    );
                }
            } elseif ($library && $request->filled('document_type_id') && $request->filled('title') && $request->filled('year')) {
                $duplicate = Document::query()
                    ->where('document_type_id', $request->integer('document_type_id'))
                    ->whereRaw('LOWER(title) = ?', [mb_strtolower(trim($request->string('title')->toString()))])
                    ->where('year', $request->integer('year'))
                    ->when($document, fn ($query) => $query->whereKeyNot($document->id))
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add(
                        'title',
                        'Referensi dengan jenis, judul, dan tahun yang sama sudah terdaftar.'
                    );
                }
            }
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    private function formData(Document $document): array
    {
        return [
            'document' => $document,
            'types' => DocumentType::orderBy('name')->get(),
            'collections' => DocumentType::COLLECTIONS,
            'categories' => LegalCategory::orderBy('name')->get(),
            'statuses' => ['berlaku' => 'Berlaku', 'dicabut' => 'Dicabut', 'diubah' => 'Diubah', 'tidak_berlaku' => 'Tidak Berlaku'],
            'accessLevels' => ['publik' => 'Publik', 'internal' => 'Internal', 'terbatas' => 'Terbatas'],
            'subfields' => ['kum' => 'Kum', 'bankum' => 'Bankum', 'sunluhkum' => 'Sunluhkum'],
        ];
    }
}
