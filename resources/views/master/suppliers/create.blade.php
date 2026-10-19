@extends('layouts.app')

@section('title', 'Tambah Supplier — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('master.suppliers.index') }}">Supplier</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Supplier</h3>
            </div>
            <form method="POST" action="{{ route('master.suppliers.store') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="code">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code') }}" maxlength="20" required>
                        @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" maxlength="150" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="contact_person">Nama Kontak</label>
                        <input type="text" name="contact_person" id="contact_person" class="form-control @error('contact_person') is-invalid @enderror"
                               value="{{ old('contact_person') }}" maxlength="100">
                        @error('contact_person') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email') }}" maxlength="100">
                                @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Telepon</label>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone') }}" maxlength="20">
                                @error('phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Alamat</label>
                        <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                        @error('address') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="tax_id">NPWP / ID Pajak</label>
                        <input type="text" name="tax_id" id="tax_id" class="form-control @error('tax_id') is-invalid @enderror"
                               value="{{ old('tax_id') }}" maxlength="30">
                        @error('tax_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="custom-control-input" name="is_active" value="1" id="is_active"
                               {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Aktif</label>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                    <a href="{{ route('master.suppliers.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection