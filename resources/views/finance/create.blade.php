@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h5>Buat Tagihan Baru</h5>
    </div>
    <div class="card-body">
        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('finance.store') }}">
            @csrf()

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="purchase_order_id" class="form-label">PO</label>
                        <select name="purchase_order_id" id="purchase_order_id" class="form-select" required>
                            <option value="">-- Pilih PO --</option>
                            @foreach ($purchaseOrders as $po)
                            <option value="{{ $po->id }}" {{ old('purchase_order_id') == $po->id ? 'selected' : '' }}>
                                PO {{ $po->display_number }} - {{ $po->supplier->name ?? 'Tanpa Supplier' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="display_number" class="form-label">Nomor Tagihan</label>
                        <input type="text" name="display_number" id="display_number" class="form-control @error('display_number') is-invalid @enderror" required>
                        @error('display_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Total Tagihan</label>
                        <input type="number" name="amount" id="amount" step="0.01" class="form-control @error('amount') is-invalid @enderror" required>
                        @error('amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="tax_amount" class="form-label">Pajak</label>
                        <input type="number" name="tax_amount" id="tax_amount" step="0.01" class="form-control @error('tax_amount') is-invalid @enderror">
                        @error('tax_amount')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="total" class="form-label">Total (termasuk pajak)</label>
                        <input type="number" name="total" id="total" step="0.01" class="form-control" readonly>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Tempo Bayar</label>
                        <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" required>
                        @error('due_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="draft">Draft</option>
                            <option value="sent">Sent</option>
                            <option value="partial">Partial</option>
                            <option value="paid">Paid</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Metode Pembayaran</label>
                        <input type="text" name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror">
                        @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Tagihan
            </button>
            <a href="{{ route('finance.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

@endsection