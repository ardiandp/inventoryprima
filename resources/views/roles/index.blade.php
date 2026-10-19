@extends('layouts.app')

@section('title', 'Role &amp; Permission — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item active">Role &amp; Permission</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Role</h3>
                <div class="card-tools">
                    <a href="{{ route('permissions.index') }}" class="btn btn-sm btn-outline-info"><i class="fas fa-key mr-1"></i> Lihat Semua Permission</a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Jumlah User</th>
                            <th>Jumlah Permission</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td><span class="badge badge-primary">{{ $role->name }}</span></td>
                            <td>{{ $role->description ?? '-' }}</td>
                            <td>{{ $role->users_count }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>
                                @if($role->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('roles.destroy', $role) }}" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus role {{ $role->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada role.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <a href="{{ route('roles.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambah Role</a>
    </div>
</div>
@endsection