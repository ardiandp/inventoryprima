@extends('layouts.app')

@section('title', 'Edit Satuan — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('master.units.index') }}">Satuan</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Edit Satuan: {{ $unit->name }}</h3>
            </div>
            <form method="POST" action="{{ route('master.units.update', $unit) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="code">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code', $unit->code) }}" maxlength="10" required>
                        @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="name">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $unit->name) }}" maxlength="50" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="symbol">Simbol <span class="text-danger">*</span></label>
                        <input type="text" name="symbol" id="symbol" class="form-control @error('symbol') is-invalid @enderror"
                               value="{{ old('symbol', $unit->symbol) }}" maxlength="10" required>
                        @error('symbol') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="custom-control custom-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="custom-control-input" name="is_active" value="1" id="is_active"
                               {{ old('is_active', $unit->is_active ? '1' : '0') === '1' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Aktif</label>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                    <a href="{{ route('master.units.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection