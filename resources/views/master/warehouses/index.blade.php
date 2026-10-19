@extends('layouts.app')

@section('title', 'Gudang — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item active">Gudang</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Gudang</h3>
                <div class="card-tools">
                    <form method="GET" action="{{ route('master.warehouses.index') }}" class="form-inline">
                        <select name="status" class="form-control form-control-sm mr-2">
                            <option value="">Semua Status</option>
                            <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control form-control-sm mr-2" placeholder="Cari kode/nama...">
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
                            <th>Penanggung Jawab</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouses as $warehouse)
                        <tr>
                            <td>{{ $warehouse->id }}</td>
                            <td><code>{{ $warehouse->code }}</code></td>
                            <td>{{ $warehouse->name }}</td>
                            <td>{{ $warehouse->contact_person ?? '-' }}</td>
                            <td><span class="badge badge-info">{{ $warehouse->locations_count }} lokasi</span></td>
                            <td>
                                @if($warehouse->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @if(auth()->user()->hasPermission('master.edit'))
                                <a href="{{ route('master.warehouses.edit', $warehouse) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                @endif
                                @if(auth()->user()->hasPermission('master.delete'))
                                <form method="POST" action="{{ route('master.warehouses.destroy', $warehouse) }}" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus gudang {{ $warehouse->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada gudang ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $warehouses->links() }}
            </div>
        </div>

        @if(auth()->user()->hasPermission('master.create'))
        <a href="{{ route('master.warehouses.create') }}" class="btn btn-primary mr-2"><i class="fas fa-plus mr-1"></i> Tambah Gudang</a>
        @endif
        @if(auth()->user()->hasPermission('master.view'))
        <a href="{{ route('master.locations.index') }}" class="btn btn-outline-secondary"><i class="fas fa-map-marker-alt mr-1"></i> Kelola Lokasi</a>
        @endif
    </div>
</div>
@endsection