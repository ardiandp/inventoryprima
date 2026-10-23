@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h5>Detail Tagihan: {{ $invoice->display_number }}</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <strong>Nomor Tagihan:</strong> {{ $invoice->display_number }}<br>
                <strong>PO:</strong> {{ $invoice->purchaseOrder->display_number ?? 'N/A' }}<br>
                <strong>Total:</strong> {{ number_format($invoice->total, 2, ',', '.') }}<br>
                <strong>Tanggal Dibuat:</strong> {{ $invoice->created_at->format('d F Y') }}<br>
                <strong>Tempo Bayar:</strong> {{ $invoice->due_date->format('d F Y') }}
            </div>
            <div class="col-md-4">
                <strong>Amount:</strong> {{ number_format($invoice->amount, 2, ',', '.') }}<br>
                <strong>Sisa:</strong> {{ number_format($invoice->amount - $invoice->payments()->sum('amount'), 2, ',', '.') }}<br>
                <strong>Status:</strong> <span class="badge {{ match($invoice->status, 'paid', 'lunas') ? 'bg-success' : (match($invoice->status, 'sent', 'partial') ? 'bg-warning' : 'bg-secondary') }}">{{ ucfirst($invoice->status) }}</span>
            </div>
            <div class="col-md-4">
                <strong>Metode Pembayaran:</strong> {{ $invoice->payment_method ?? 'Belum ada' }}<br>
                <strong>Dibayar Oleh:</strong> {{ $invoice->paidBy ? $invoice->paidBy->name : 'Belum dipilih' }}<br>
                <strong>Tanggal Bayar:</strong> {{ $invoice->payments->last()?->paid_at ? $invoice->payments->last()?->paid_at->format('d F Y H:i') : 'Belum ada' }}
            </div>
        </div>

        <h6>Riwayat Pembayaran</h6>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Jumlah</th>
                    <th>Metode</th>
                    <th>Referensi</th>
                    <th>Dibayar Oleh</th>
                    <strong>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->payments as $payment)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ number_format($payment->amount, 2, ',', '.') }}</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>{{ $payment->reference_number ?? '-' }}</td>
                    <td>{{ $payment->paidBy ? $payment->paidBy->name : 'Sistem' }}</td>
                    <td>{{ $payment->paid_at->format('d F Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($canPay)
        <div class="mt-3">
            <h6>Proses Pembayaran</h6>
            <p>Sisa yang harus dibayar: <strong>{{ number_format($invoice->amount - $invoice->payments()->sum('amount'), 2, ',', '.') }}</strong></p>
            <form method="POST" action="{{ route('finance.process-payment', $invoice->id) }}">
                @csrf()
                @method('POST')

                <div class="mb-3">
                    <label for="amount" class="form-label">Jumlah Pembayaran</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="1" max="{{ $invoice->amount }}" class="form-control" required>
                    <small class="text-muted">Maksimal: {{ number_format($invoice->amount, 2, ',', '.') }}</small>
                </div>

                <div class="mb-3">
                    <label for="reference_number" class="form-label">Nomor Referensi</label>
                    <input type="text" name="reference_number" id="reference_number" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Catatan</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Catat Pembayaran
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

@endsection