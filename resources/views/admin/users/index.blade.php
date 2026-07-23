@extends('layouts.admin')

@section('title', 'Kelola Pengguna')
@section('page_title', 'Kelola Pengguna')

@section('page_actions')
    <a class="btn btn-primary" href="{{ route('admin.users.create') }}"><i class="bi bi-person-plus"></i> Tambah Pengguna</a>
@endsection

@section('content')
<div class="content-card p-3">
    <form method="get" class="search-toolbar mb-3">
        <div class="input-group flex-grow-1">
            <span class="input-group-text"><i data-lucide="search"></i></span>
            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau satuan kerja">
        </div>
        <select class="form-select" name="role" aria-label="Filter role pengguna">
            <option value="">Semua role</option>
            <option value="super_admin" @selected(request('role') === 'super_admin')>Super Admin</option>
            <option value="admin" @selected(request('role') === 'admin')>Admin</option>
            <option value="internal" @selected(request('role') === 'internal')>Internal</option>
        </select>
        <button class="btn btn-outline-secondary" type="submit"><i data-lucide="filter"></i> Filter</button>
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Pengguna</th><th>Role</th><th>Satuan Kerja</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $user->name }}</div>
                        <small class="text-muted">{{ $user->email }} @if($user->jabatan) | {{ $user->jabatan }} @endif</small>
                    </td>
                    <td>{{ ['super_admin' => 'Super Admin', 'admin' => 'Admin', 'internal' => 'Internal'][$user->role] }}</td>
                    <td>{{ $user->satuan_kerja ?: '-' }}</td>
                    <td><span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user) }}"><i class="bi bi-pencil"></i></a>
                        @unless(auth()->user()->is($user))
                            <form class="d-inline" method="post" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Akun pengguna ini akan dihapus dari sistem. Pastikan akun tidak lagi digunakan." data-confirm-title="Hapus Pengguna" data-confirm-label="Ya, Hapus" data-confirm-variant="danger">
                                @csrf
                                @method('delete')
                                <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bi bi-trash"></i></button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted">Belum ada pengguna.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection
