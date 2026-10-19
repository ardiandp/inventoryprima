@extends('layouts.app')

@section('title', 'Lokasi Gudang — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item active">Lokasi Gudang</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Lokasi Gudang</h3>
                <div class="card-tools">
                    <form method="GET" action="{{ route('master.locations.index') }}" class="form-inline">
                        <select name="warehouse_id" class="form-control form-control-sm mr-2">
                            <option value="">Semua Gudang</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ ($warehouseId ?? null) == $wh->id ? 'selected' : '' }}>
                                {{ $wh->name }}
                            </option>
                            @endforeach
                        </select>
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
                            <th>Gudang</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $location)
                        <tr>
                            <td>{{ $location->id }}</td>
                            <td>{{ $location->warehouse?->name ?? '-' }}</td>
                            <td><code>{{ $location->code }}</code></td>
                            <td>{{ $location->name }}</td>
                            <td>
                                @if($location->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @if(auth()->user()->hasPermission('master.edit'))
                                <a href="{{ route('master.locations.edit', $location) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                @endif
                                @if(auth()->user()->hasPermission('master.delete'))
                                <form method="POST" action="{{ route('master.locations.destroy', $location) }}" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus lokasi {{ $location->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Tidak ada lokasi ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $locations->links() }}
            </div>
        </div>

        @if(auth()->user()->hasPermission('master.create'))
        <a href="{{ route('master.locations.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambah Lokasi</a>
        @endif
        <a href="{{ route('master.warehouses.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Kembali ke Gudang</a>
    </div>
</div>
@endsection