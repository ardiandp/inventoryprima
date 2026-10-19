@extends('layouts.app')

@section('title', 'Edit Lokasi — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('master.locations.index') }}">Lokasi Gudang</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Edit Lokasi: {{ $location->name }}</h3>
            </div>
            <form method="POST" action="{{ route('master.locations.update', $location) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="warehouse_id">Gudang <span class="text-danger">*</span></label>
                        <select name="warehouse_id" id="warehouse_id" class="form-control @error('warehouse_id') is-invalid @enderror" required>
                            <option value="">— Pilih Gudang —</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id', $location->warehouse_id) == $wh->id ? 'selected' : '' }}>
                                {{ $wh->code }} — {{ $wh->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('warehouse_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="code">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code', $location->code) }}" maxlength="20" required>
                        @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $location->name) }}" maxlength="100" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea name="description" id="description" rows="2" class="form-control @error('description') is-invalid @enderror">{{ old('description', $location->description) }}</textarea>
                        @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="custom-control-input" name="is_active" value="1" id="is_active"
                               {{ old('is_active', $location->is_active ? '1' : '0') === '1' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Aktif</label>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                    <a href="{{ route('master.locations.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection