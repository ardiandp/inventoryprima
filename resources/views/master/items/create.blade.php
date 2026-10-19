@extends('layouts.app')

@section('title', 'Tambah Barang — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('master.items.index') }}">Barang</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Barang</h3>
            </div>
            <form method="POST" action="{{ route('master.items.store') }}">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code">Kode <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code') }}" maxlength="30" required>
                                @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nama <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" maxlength="150" required>
                                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id">Kategori <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                    <option value="">— Pilih Kategori —</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->code }} — {{ $cat->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="default_unit_id">Satuan <span class="text-danger">*</span></label>
                                <select name="default_unit_id" id="default_unit_id" class="form-control @error('default_unit_id') is-invalid @enderror" required>
                                    <option value="">— Pilih Satuan —</option>
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('default_unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->code }} — {{ $unit->name }} ({{ $unit->symbol }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('default_unit_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="specification">Spesifikasi</label>
                        <textarea name="specification" id="specification" rows="3" class="form-control @error('specification') is-invalid @enderror">{{ old('specification') }}</textarea>
                        @error('specification') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="min_stock">Stok Minimum <span class="text-danger">*</span></label>
                                <input type="number" name="min_stock" id="min_stock" step="0.0001" min="0"
                                       class="form-control @error('min_stock') is-invalid @enderror"
                                       value="{{ old('min_stock', 0) }}" required>
                                @error('min_stock') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="max_stock">Stok Maksimum</label>
                                <input type="number" name="max_stock" id="max_stock" step="0.0001" min="0"
                                       class="form-control @error('max_stock') is-invalid @enderror"
                                       value="{{ old('max_stock') }}">
                                @error('max_stock') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-switch mb-2">
                                    <input type="hidden" name="requires_qc" value="0">
                                    <input type="checkbox" class="custom-control-input" name="requires_qc" value="1" id="requires_qc"
                                           {{ old('requires_qc', '0') === '1' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="requires_qc">Wajib QC</label>
                                </div>
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" class="custom-control-input" name="is_active" value="1" id="is_active"
                                           {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">Aktif</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                    <a href="{{ route('master.items.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection