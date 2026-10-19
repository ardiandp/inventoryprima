@extends('layouts.app')

@section('title', 'Barang — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item active">Barang</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Barang</h3>
                <div class="card-tools">
                    <form method="GET" action="{{ route('master.items.index') }}" class="form-inline">
                        <select name="category_id" class="form-control form-control-sm mr-2">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($categoryId ?? null) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
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
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Min. Stok</th>
                            <th>QC</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td><code>{{ $item->code }}</code></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category?->name ?? '-' }}</td>
                            <td>{{ $item->defaultUnit?->name ?? '-' }} ({{ $item->defaultUnit?->symbol ?? '-' }})</td>
                            <td>{{ rtrim(rtrim(number_format((float) $item->min_stock, 4, '.', ','), '0'), '.') }}</td>
                            <td>
                                @if($item->requires_qc)
                                    <span class="badge badge-warning">Wajib QC</span>
                                @else
                                    <span class="badge badge-light">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @if(auth()->user()->hasPermission('master.edit'))
                                <a href="{{ route('master.items.edit', $item) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                @endif
                                @if(auth()->user()->hasPermission('master.delete'))
                                <form method="POST" action="{{ route('master.items.destroy', $item) }}" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus barang {{ $item->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Tidak ada barang ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $items->links() }}
            </div>
        </div>

        @if(auth()->user()->hasPermission('master.create'))
        <a href="{{ route('master.items.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambah Barang</a>
        @endif
    </div>
</div>
@endsection