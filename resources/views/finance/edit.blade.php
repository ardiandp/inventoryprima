@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h5>Edit Tagihan: {{ $invoice->display_number }}</h5>
    </div>
    <div class="card-body">
        @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="purchase_order_id" class="form-label">PO</label>
                        <select name="purchase_order_id" id="purchase_order_id" class="form-select">
                            <option value="">-- Pilih PO --</option>
                            @foreach ($purchaseOrders as $po)
                            <option value="{{ $po->id }}" {{ old('purchase_order_id') == $po->id ? 'selected' : '' }} {{ $invoice->purchase_order_id == $po->id ? 'disabled' : '' }}>
                                PO {{ $po->display_number }} - {{ $po->supplier->name ?? 'Tanpa Supplier' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="display_number" class="form-label">Nomor Tagihan</label>
                        <input type="text" name="display_number" id="display_number" class="form-control @error('display_number') is-invalid @enderror" value="{{ old('display_number', $invoice->display_number) }}" required>
                        @error('display_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>