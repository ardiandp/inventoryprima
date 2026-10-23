@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h5>Daftar Tagihan</h5>
    </div>
    <div class="card-body">
        <a href="{{ route('finance.create') }}" class="btn btn-primary mb-3">
            <i class="fas fa-plus"></i> Buat Tagihan Baru
        </a>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nomor Tagihan</th>
                    <th>PO</th>
                    <th>Total</th>
                    <th>Sisa</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoices as $invoice)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $invoice->display_number }}</td>
                    <td>{{ $invoice->purchaseOrder->display_number ?? 'N/A' }}</td>
                    <td>{{ number_format($invoice->total, 2, ',', '.') }}</td>
                    <td>{{ number_format($invoice->amount - $invoice->payments()->sum('amount'), 2, ',', '.') }}</td>
                    <td>
                        <span class="badge {{ match($invoice->status, 'paid', 'lunas') ? 'bg-success' : (match($invoice->status, 'sent', 'partial') ? 'bg-warning' : 'bg-secondary') }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('finance.show', $invoice->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('finance.edit')
                        <a href="{{ route('finance.edit', $invoice->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection