@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Permintaan Pembelian (RPO)</h3>
        <a href="{{ route('rpo.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Buat RPO Baru
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No RPO</th>
                        <th>Tanggal</th>
                        <th>Petugas</th>
                        <th>Status</th>
                        <th>Item</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchaseRequests as $index => $pr)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $pr->display_number }}</td>
                        <td>{{ $pr->created_at->format('d M Y') }}</td>
                        <td>{{ $pr->requestedBy->name ?? '---' }}</td>
                        <td>
                            <span class="badge bg-{{ $pr->status === 'approved' ? 'success' : ($pr->status === 'revision_requested' ? 'warning' : 'secondary') }}">
                                {{ __('status.' . $pr->status, [], 'app/models/PurchaseRequest') }}
                            </span>
                        </td>
                        <td>
                            @if($pr->items()->count() > 0)
                            {{ $pr->items()->count() }} item
                            @else
                            <span class="text-danger">0 item</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('rpo.show', $pr->id) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection