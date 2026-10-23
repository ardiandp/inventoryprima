@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Detail Inspeksi Kualitas</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>No. Inspeksi:</strong> {{ $inspection->display_number }}<br>
                        <strong>PO:</strong> {{ $inspection->purchaseOrder->display_number ?? 'N/A' }}<br>
                        <strong>Tanggal Terima:</strong> {{ $inspection->receipt->display_number ?? 'N/A' }}<br>
                        <strong>Tanggal Inspeksi:</strong> {{ $inspection->inspected_at ? format_date($inspection->inspected_at) : 'Belum diperiksa' }}<br>
                        <strong>Diperiksa Oleh:</strong> {{ $inspection->inspectedBy ? $inspection->inspectedBy->name : 'Belum ditetapkan' }}<br>
                        <strong>Observasi:</strong> {{ $inspection->observations ?? '-' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong> <span class="badge bg-{{ match($inspection->status) {
                            case 'accepted': 'success';
                            case 'rejected': 'danger';
                            case 'partially_accepted': 'warning';
                            case 'posted': 'info';
                            default: 'secondary';
                        } }}">{{ $inspection->getStatusLabelAttribute }}</strong><br>
                        <strong>Accepted:</strong> {{ number_format($inspection->accepted_quantity, 2, ',', '.') }}<br>
                        <strong>Rejected:</strong> {{ number_format($inspection->rejected_quantity, 2, ',', '.') }}
                    </div>
                </div>

                @can('update', $inspection)
                <hr>
                <h5>Aksi</h5>
                <div class="row">
                    @can('accept', $inspection)
                    <div class="col-md-4">
                        <a href="{{ route('qc.accept', $inspection->id) }}" class="btn btn-success w-100"
                           onclick="return confirm('Yakin mengubah status menjadi Lolos QC?')">
                            <i class="fas fa-check"></i> Terima Lolos QC
                        </a>
                    </div>
                    @endcan

                    @can('reject', $inspection)
                    <div class="col-md-4">
                        <a href="{{ route('qc.reject', $inspection->id) }}" class="btn btn-danger w-100"
                           onclick="return confirm('Yakin mengubah status menjadi Ditolak QC?')">
                            <i class="fas fa-times"></i> Tolak QC
                        </a>
                    </div>
                    @endcan

                    @can('post', $inspection)
                    <div class="col-md-4">
                        <a href="{{ route('qc.post', $inspection->id) }}" class="btn btn-info w-100"
                           onclick="return confirm('Yakin memposting status ini? Tindakan ini mempengaruhi stok.')">
                            <i class="fas fa-upload"></i> Posting
                        </a>
                    </div>
                    @endcan
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection