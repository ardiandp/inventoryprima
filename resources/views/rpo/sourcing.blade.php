@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Sourcing RPO: {{ $purchaseRequest->display_number }}</h3>
    </div>
    <div class="card-body">
        <p>Pilih supplier untuk RPO ini.</p>

        @if($purchaseRequest->status !== 'draft')
            <p class="text-warning">Hanya RPO dalam status Draft yang bisa dimulai sourcing.</p>
        @endif

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Detail RPO</h5>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">No RPO</dt>
                            <dd class="col-sm-8">{{ $purchaseRequest->display_number }}</dd>

                            <dt class="col-sm-4">Tanggal Diajukan</dt>
                            <dd class="col-sm-8">{{ $purchaseRequest->created_at->format('d M Y') }}</dd>

                            <dt class="col-sm-4">Departemen</dt>
                            <dd class="col-sm-8">{{ $purchaseRequest->department->name }}</dd>

                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                <span class="badge bg-{{ match($purchaseRequest->status) 
                                    case 'draft' : 'secondary'
                                    case 'submitted' : 'info'
                                    case 'under_review' : 'warning'
                                    case 'revision_requested' : 'secondary'
                                    case 'sourcing' : 'primary'
                                    case 'pending_approval' : 'warning'
                                    case 'approved' : 'success'
                                    case 'rejected' : 'danger'
                                    case 'cancelled' : 'secondary'
                                }}">{{ __('status.' . $purchaseRequest->status, [], 'app/models/PurchaseRequest') }}</span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Pilih Supplier</h5>
                    </div>
                    <div class="card-body">
                        @if($purchaseRequest->status === 'draft')
                        <form action="{{ route('rpo.sourcing.store', $purchaseRequest->id) }}" method="POST">
                            @csrf()

                            <div class="mb-3">
                                <label for="supplier_id" class="form-label">Supplier</label>
                                <select name="supplier_id" id="supplier_id" class="form-select" required>
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Catatan pilihan supplier"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Mulai Sourcing</button>
                            <a href="{{ route('rpo.show', $purchaseRequest->id) }}" class="btn btn-secondary">Batal</a>
                        </form>
                        @else
                        <p>Sourcing sudah dimulai untuk RPO ini.</p>
                        <a href="{{ route('rpo.show', $purchaseRequest->id) }}" class="btn btn-primary">Kembali ke RPO</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection