@extends('layouts.app')

@section('title', 'Supplier — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item active">Supplier</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Supplier</h3>
                <div class="card-tools">
                    <form method="GET" action="{{ route('master.suppliers.index') }}" class="form-inline">
                        <select name="status" class="form-control form-control-sm mr-2">
                            <option value="">Semua Status</option>
                            <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control form-control-sm mr-2" placeholder="Cari kode/nama/kontak...">
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kontak</th>
                            <th>Telepon</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->id }}</td>
                            <td><code>{{ $supplier->code }}</code></td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->contact_person ?? '-' }}</td>
                            <td>{{ $supplier->phone ?? '-' }}</td>
                            <td>
                                @if($supplier->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @if(auth()->user()->hasPermission('master.edit'))
                                <a href="{{ route('master.suppliers.edit', $supplier) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                @endif
                                @if(auth()->user()->hasPermission('master.delete'))
                                <form method="POST" action="{{ route('master.suppliers.destroy', $supplier) }}" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus supplier {{ $supplier->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada supplier ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $suppliers->links() }}
            </div>
        </div>

        @if(auth()->user()->hasPermission('master.create'))
        <a href="{{ route('master.suppliers.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambah Supplier</a>
        @endif
    </div>
</div>
@endsection