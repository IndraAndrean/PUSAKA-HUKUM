<x-mail::message>
# Konsultasi Anda Telah Dijawab

Halo {{ $consultation->name }},

Pertanyaan yang Anda ajukan pada {{ $consultation->created_at->translatedFormat('d F Y') }} telah dijawab oleh tim Bidang Hukum Polda Lampung.

**Kode pelacakan:** {{ $consultation->tracking_code }}

**Pertanyaan Anda:**
{{ $consultation->question }}

**Jawaban:**
{{ $consultation->answer }}

<x-mail::button :url="route('consultation.status', ['tracking_code' => $consultation->tracking_code])">
Lihat Detail Jawaban
</x-mail::button>

Terima kasih telah menggunakan layanan Konsultasi Informasi Hukum PUSAKA HUKUM.

Salam,<br>
Bidang Hukum Polda Lampung
</x-mail::message>
