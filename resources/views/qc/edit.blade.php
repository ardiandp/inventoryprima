@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Edit Inspeksi Kualitas</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('qc.update', $inspection->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="display_number" value="{{ $inspection->display_number }}">

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">No. Penerimaan</label>
                        <div class="col-sm-8">
                            <select name="receipt_id" class="form-control" required>
                                <option value="">-- Pilih Penerimaan --</option>
                                @foreach($pendingReceptions as $receipt)
                                <option value="{{ $receipt->id }}" {{ $inspection->receipt_id == $receipt->id ? 'selected' : '' }}>
                                    {{ $receipt->display_number }} - {{ $receipt->purchaseOrder->display_number }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">PO</label>
                        <div class="col-sm-8">
                            <select name="purchase_order_id" class="form-control" required>
                                <option value="">-- Pilih PO --</option>
                                @foreach($pendingReceptions as $receipt)
                                <option value="{{ $receipt->purchase_order_id }}" {{ $inspection->purchase_order_id == $receipt->purchase_order_id ? 'selected' : '' }}>
                                    {{ $receipt->purchaseOrder->display_number }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">Tanggal Inspeksi</label>
                        <div class="col-sm-8">
                            <input type="date" name="inspected_at" class="formctlrol" value="{{ $inspection->inspected_at ?? old('inspected_at') }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">Quantity Diterima</label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" name="accepted_quantity" class="form-control" value="{{ $inspection->accepted_quantity ?? old('accepted_quantity') }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">Quantity Ditolak</label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" name="rejected_quantity" class="form-control" value="{{ $inspection->rejected_quantity ?? old('rejected_quantity') }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">Observasi</label>
                        <div class="col-sm-8">
                            <textarea name="observations" class="form-control" rows="3">{{ $inspection->observations ?? old('observations') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                        <a href="{{ route('qc.show', $inspection->id) }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection