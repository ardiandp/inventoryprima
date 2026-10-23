@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Detail RPO: {{ $purchaseRequest->display_number }}</h3>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">No RPO</dt>
            <dd class="col-sm-9">{{ $purchaseRequest->display_number }}</dd>

            <dt class="col-sm-3">Tanggal Diajukan</dt>
            <dd class="col-sm-9">{{ $purchaseRequest->created_at->format('d M Y') }}</dd>

            <dt class="col-sm-3">Petugas</dt>
            <dd class="col-sm-9">{{ $purchaseRequest->requestedBy->name }}</dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">
                <span class="badge bg-{{ $purchaseRequest->status === 'approved' ? 'success' : ($purchaseRequest->status === 'revision_requested' ? 'warning' : 'secondary') }}">
                    {{ $purchaseRequest->status_label }}
                </span>
            </dd>

            <dt class="col-sm-3">Prioritas</dt>
            <dd class="col-sm-9">{{ $purchaseRequest->priority }}</dd>

            <dt class="col-sm-3">Jenis Permintaan</dt>
            <dd class="col-sm-9">{{ $purchaseRequest->request_type }}</dd>

            <dt class="col-sm-3">Departemen</dt>
            <dd class="col-sm-9">{{ $purchaseRequest->department->name ?? '---' }}</dd>

            <dt class="col-sm-3">Total Estimasi Nilai</dt>
            <dd class="col-sm-9">Rp {{ number_format($purchaseRequest->totalEstimatedValue, 2, ',', '.') }}</dd>
        </dl>

        @if($purchaseRequest->items->isNotEmpty)
        <h4>Item RPO</h4>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Spesifikasi</th>
                        <th>Qty</th>
                        <th>Harga Satuan</th>
                        <th>Total</th>
                        <th>Status Item</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseRequest->items as $item)
                    <tr>
                        <td>{{ $item->item->name }}</td>
                        <td>{{ $item->specification }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>Rp {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                        <td>Rp {{ number_format($item->quantity * $item->unit_price, 2, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : 'secondary') }}">
                                {{ $item->status_label }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted">Tidak ada item pada RPO ini.</p>
        @endif
    </div>
</div>
@endsection