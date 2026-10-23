@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Daftar Inspeksi Kualitas</h4>
            </div>
            <div class="card-body">
                <a href="{{ route('qc.create') }}" class="btn btn-success mb-3">
                    <i class="fas fa-plus"></i> Buat Inspeksi Baru
                </a>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No. Inspeksi</th>
                            <th>PO</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inspections as $index => $inspection)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $inspection->display_number }}</td>
                            <td>{{ $inspection->purchaseOrder->display_number ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ match($inspection->status) {
                                    case 'accepted': 'success';
                                    case 'rejected': 'danger';
                                    case 'partially_accepted': 'warning';
                                    case 'posted': 'info';
                                    default: 'secondary';
                               } }}">{{ $inspection->getStatusLabelAttribute }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('qc.show', $inspection->id) }}" class="btn btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    @can('update', $inspection)
                                        <a href="{{ route('qc.edit', $inspection->id) }}" class="btn btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                    @endcan
                                    @can('accept', $inspection)
                                        <a href="{{ route('qc.accept', $inspection->id) }}" class="btn btn-outline-success" onclick="return confirm('Yakin mengubah status menjadi Lolos QC?')"><i class="fas fa-check"></i></a>
                                    @endcan
                                    @can('reject', $inspection)
                                        <a href="{{ route('qc.reject', $inspection->id) }}" class="btn btn-outline-danger" onclick="return confirm('Yakin mengubah status menjadi Ditolak QC?')"><i class="fas fa-times"></i></a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection