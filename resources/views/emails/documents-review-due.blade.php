<x-mail::message>
# Dokumen Jatuh Tempo Tinjau Ulang

Halo,

Berikut {{ $documents->count() }} dokumen di PUSAKA HUKUM yang sudah melewati jadwal tinjau ulangnya dan perlu diperiksa kembali kelengkapan/keakuratannya.

<x-mail::table>
| Dokumen | Jenis | Jatuh Tempo |
| :------ | :---- | :---------- |
@foreach ($documents as $document)
| {{ $document->title }} | {{ $document->type?->name }} | {{ $document->next_review_at?->translatedFormat('d F Y') }} |
@endforeach
</x-mail::table>

<x-mail::button :url="route('admin.documents.index')">
Buka Manajemen Dokumen
</x-mail::button>

Email ini dikirim otomatis tiap minggu oleh sistem PUSAKA HUKUM.

Salam,<br>
Bidang Hukum Polda Lampung
</x-mail::message>
