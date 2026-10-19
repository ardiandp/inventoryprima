@extends('layouts.app')

@section('title', 'Pengguna — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item active">Pengguna</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Pengguna</h3>
                <div class="card-tools">
                    <form method="GET" action="{{ route('users.index') }}" class="form-inline">
                        <input type="text" name="q" value="{{ $search }}" class="form-control form-control-sm mr-2" placeholder="Cari nama/email...">
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Departemen</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->department?->name ?? '-' }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge badge-info">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus pengguna {{ $user->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada pengguna ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        </div>

        <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambah Pengguna</a>
    </div>
</div>
@endsection