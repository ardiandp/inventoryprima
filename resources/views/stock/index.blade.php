@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="mb-3">
        <h1><i class="fas fa-box-open me-2"></i>Transaksi Stok</h1>
        <p class="text-muted">Management stok barang dan pengeluaran untuk produksi</p>
    </div>
</div>

<div class="card">
    <div class="card-header card-header-tabs">
        <h4 class="card-title">Daftar Transaksi Stok</h4>
    </div>
    <!-- /.card-header -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table m-0">
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Penerimaan/PO</th>
                        <th>Issue Qty</th>
                        <th>Sisa Stok</th>
                        <th>Status</th>
                        <th>Diposting</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->display_number }}</td>
                        <td>
                            @if($transaction->purchase_receipt_id)
                                <span class="badge bg-primary">RN-{{ $transaction->purchase_receipt_id }}</span>
                            @elseif($transaction->purchase_order_id)
                                <span class="badge bg-info">PO-{{ $transaction->purchase_order_id }}</span>
                            @endif
                        </td>
                        <td>{{ $transaction->issue_quantity }} {{ $transaction->items->first()?->unit ?? 'pcs' }}</td>
                        <td>{{ $transaction->remaining_quantity }} {{ $transaction->items->first()?->unit ?? 'pcs' }}</td>
                        <td>
                            <span class="badge bg-{{ match($transaction->status) { case 'draft': 'secondary'; case 'posted': 'success'; case 'cancelled': 'danger'; default: 'secondary' } }}{{ $transaction->status }}</span>
                        </td>
                        <td>
                            @if($transaction->status === 'draft')
                                <a href="{{ route('stock-transactions.post', $transaction->id) }}" class="btn btn-sm btn-success">Post</a>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('stock-transactions.show', $transaction->id) }}" class="btn btn-sm btn-outline-secondary">Lihat</a>
                            @can('update', $transaction)
                                <a href="{{ route('stock-transactions.edit', $transaction->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            @endcan
                            @can('cancel', $transaction)
                                <form action="{{ route('stock-transactions.cancel', $transaction->id) }}" method="POST" style="display:inline"
                                    onsubmit="return confirm('Yakin ingin membatalkan transaksi ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Batal</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header">
        <h4 class="card-title">Filter</h4>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('stock-transactions.index') }}" class="row g-3">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posting</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="receipt_id" class="form-select">
                    <option value="">Semua Penerimaan</option>
                    @foreach ($receipts as $receipt)
                        <option value="{{ $receipt->id }}" {{ request('receipt_id') == $receipt->id ? 'selected' : '' }}>
                            RN-{{ $receipt->id }} - {{ $receipt->display_number ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('stock-transactions.create') }}" class="btn btn-success w-100">Transaksi Baru</a>
            </div>
        </form>
    </div>
</div>
@endsection