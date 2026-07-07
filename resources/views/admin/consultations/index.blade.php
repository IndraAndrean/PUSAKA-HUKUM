@extends('layouts.admin')

@section('title', 'Kelola Konsultasi')
@section('page_title', 'Kelola Konsultasi')

@section('content')
<div class="content-card p-3">
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-8">
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau isi pertanyaan">
        </div>
        <div class="col-md-2">
            <select class="form-select" name="status">
                <option value="">Semua status</option>
                @foreach(['masuk' => 'Masuk', 'diproses' => 'Diproses', 'dijawab' => 'Dijawab', 'selesai' => 'Selesai'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Filter</button>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Pengirim</th><th>Pertanyaan</th><th>Status</th><th>Tanggal</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($consultations as $consultation)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $consultation->name }}</div>
                        <small class="text-muted">{{ $consultation->email ?: '-' }}</small>
                    </td>
                    <td>{{ str($consultation->question)->limit(100) }}</td>
                    <td><span class="badge text-bg-light">{{ ucfirst($consultation->status) }}</span></td>
                    <td>{{ $consultation->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.consultations.show', $consultation) }}"><i class="bi bi-chat-left-text"></i> Tanggapi</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted">Belum ada konsultasi masuk.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $consultations->links() }}
</div>
@endsection
