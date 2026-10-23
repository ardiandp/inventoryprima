@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Buat Inspeksi Kualitas Baru</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('qc.store') }}" method="POST">
                    @csrf
                    @method('POST')

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">No. Penerimaan</label>
                        <div class="col-sm-8">
                            <select name="receipt_id" class="form-control" required>
                                <option value="">-- Pilih Penerimaan --</option>
                                @foreach($pendingReceptions as $receipt)
                                <option value="{{ $receipt->id }}">{{ $receipt->display_number }} - {{ $receipt->purchaseOrder->display_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">PO</label>
                        <div class="col-sm-8">
                            <select name="purchase_order_id" class="form-control" id="poSelect" required>
                                <option value="">-- Pilih PO --</option>
                                @foreach($pendingReceptions as $receipt)
                                <option value="{{ $receipt->purchase_order_id }}">{{ $receipt->purchaseOrder->display_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">Tanggal Inspeksi</label>
                        <div class="col-sm-8">
                            <input type="date" name="inspected_at" class="form-control" value="{{ old('inspected_at') }}" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">Diperiksa Oleh</label>
                        <div class="col-sm-8">
                            <input type="text" name="inspected_by" class="form-control" value="{{ auth()->user()->name ?? old('inspected_by') }}" readonly>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">Quantity Diterima</label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" name="accepted_quantity" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">Quantity Ditolak</label>
                        <div class="col-sm-8">
                            <input type="number" step="0.01" name="rejected_quantity" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">Observasi</label>
                        <div class="col-sm-8">
                            <textarea name="observations" class="form-control" rows="3">{{ old('observations') }}</textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-form-label col-sm-3">No. Display</label>
                        <div class="col-sm-8">
                            <input type="text" name="display_number" class="form-control" placeholder="OTOMATIS" readonly>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Inspeksi
                        </button>
                        <a href="{{ route('qc.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection