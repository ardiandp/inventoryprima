@extends('layouts.app')

@section('title', 'Proyek — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item active">Proyek</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Proyek / Work Order</h3>
                <div class="card-tools">
                    <form method="GET" action="{{ route('master.projects.index') }}" class="form-inline">
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
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Tahap</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        <tr>
                            <td>{{ $project->id }}</td>
                            <td><code>{{ $project->code }}</code></td>
                            <td>{{ $project->name }}</td>
                            <td>
                                {{ $project->start_date?->format('d/m/Y') ?? '-' }}
                                s/d
                                {{ $project->end_date?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td>
                                @if($project->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @if($project->start_date && $project->end_date)
                                    @if(now()->between($project->start_date, $project->end_date))
                                        <span class="badge badge-primary">Berjalan</span>
                                    @elseif(now()->lt($project->start_date))
                                        <span class="badge badge-info">Akan Datang</span>
                                    @else
                                        <span class="badge badge-warning">Selesai</span>
                                    @endif
                                @else
                                    <span class="badge badge-light">-</span>
                                @endif
                            </td>
                            <td>
                                @if(auth()->user()->hasPermission('master.edit'))
                                <a href="{{ route('master.projects.edit', $project) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                @endif
                                @if(auth()->user()->hasPermission('master.delete'))
                                <form method="POST" action="{{ route('master.projects.destroy', $project) }}" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus proyek {{ $project->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Tidak ada proyek ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $projects->links() }}
            </div>
        </div>

        @if(auth()->user()->hasPermission('master.create'))
        <a href="{{ route('master.projects.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambah Proyek</a>
        @endif
    </div>
</div>
@endsection