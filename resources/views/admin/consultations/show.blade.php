@extends('layouts.admin')

@section('title', 'Tanggapi Konsultasi')
@section('page_title', 'Tanggapi Konsultasi')

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="content-card p-3 h-100">
            <h2 class="h5">Pertanyaan Pengguna</h2>
            <dl class="row mb-0">
                <dt class="col-sm-4">Nama</dt><dd class="col-sm-8">{{ $consultation->name }}</dd>
                <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $consultation->email ?: '-' }}</dd>
                <dt class="col-sm-4">Tanggal</dt><dd class="col-sm-8">{{ $consultation->created_at->format('d/m/Y H:i') }}</dd>
                <dt class="col-sm-4">Status</dt><dd class="col-sm-8">{{ ucfirst($consultation->status) }}</dd>
            </dl>
            <hr>
            <div style="white-space: pre-line;">{{ $consultation->question }}</div>
        </div>
    </div>
    <div class="col-lg-7">
        <form class="content-card p-3" method="post" action="{{ route('admin.consultations.update', $consultation) }}">
            @csrf
            @method('put')
            <div class="mb-3">
                <label class="form-label" for="answer">Jawaban atau Rujukan Dokumen</label>
                <textarea class="form-control" id="answer" name="answer" rows="10" placeholder="Contoh: Berdasarkan dokumen Peraturan Kapolri Nomor ..., Bapak/Ibu dapat merujuk pada Pasal ... terkait ...">{{ old('answer', $consultation->answer) }}</textarea>
                <div class="form-text">Berikan jawaban yang jelas, sertakan rujukan dokumen bila ada, dan hindari memuat data perkara atau informasi rahasia.</div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    @foreach(['masuk' => 'Masuk', 'diproses' => 'Diproses', 'dijawab' => 'Dijawab', 'selesai' => 'Selesai'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $consultation->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="form-text">Gunakan Diproses saat masih ditelaah, Dijawab setelah tanggapan diberikan, dan Selesai jika tidak ada tindak lanjut.</div>
            </div>
            @if($consultation->answerer)
                <p class="small text-muted">Terakhir dijawab oleh {{ $consultation->answerer->name }} pada {{ $consultation->answered_at?->format('d/m/Y H:i') }}.</p>
            @endif
            <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Simpan Tanggapan</button>
        </form>
        <form class="mt-3" method="post" action="{{ route('admin.consultations.destroy', $consultation) }}" onsubmit="return confirm('Hapus konsultasi ini?')">
            @csrf
            @method('delete')
            <button class="btn btn-outline-danger" type="submit"><i class="bi bi-trash"></i> Hapus Konsultasi</button>
        </form>
    </div>
</div>
@endsection
