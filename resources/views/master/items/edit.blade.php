@extends('layouts.app')

@section('title', 'Edit Barang — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('master.items.index') }}">Barang</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Edit Barang: {{ $item->name }}</h3>
            </div>
            <form method="POST" action="{{ route('master.items.update', $item) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="code">Kode <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                                       value="{{ old('code', $item->code) }}" maxlength="30" required>
                                @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Nama <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $item->name) }}" maxlength="150" required>
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
                                    <option value="{{ $cat->id }}" {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>
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
                                    <option value="{{ $unit->id }}" {{ old('default_unit_id', $item->default_unit_id) == $unit->id ? 'selected' : '' }}>
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
                        <textarea name="specification" id="specification" rows="3" class="form-control @error('specification') is-invalid @enderror">{{ old('specification', $item->specification) }}</textarea>
                        @error('specification') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="min_stock">Stok Minimum <span class="text-danger">*</span></label>
                                <input type="number" name="min_stock" id="min_stock" step="0.0001" min="0"
                                       class="form-control @error('min_stock') is-invalid @enderror"
                                       value="{{ old('min_stock', $item->min_stock) }}" required>
                                @error('min_stock') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="max_stock">Stok Maksimum</label>
                                <input type="number" name="max_stock" id="max_stock" step="0.0001" min="0"
                                       class="form-control @error('max_stock') is-invalid @enderror"
                                       value="{{ old('max_stock', $item->max_stock) }}">
                                @error('max_stock') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-switch mb-2">
                                    <input type="hidden" name="requires_qc" value="0">
                                    <input type="checkbox" class="custom-control-input" name="requires_qc" value="1" id="requires_qc"
                                           {{ old('requires_qc', $item->requires_qc ? '1' : '0') === '1' ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="requires_qc">Wajib QC</label>
                                </div>
                                <div class="custom-control custom-switch">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" class="custom-control-input" name="is_active" value="1" id="is_active"
                                           {{ old('is_active', $item->is_active ? '1' : '0') === '1' ? 'checked' : '' }}>
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


        {{-- Konversi Satuan --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Konversi Satuan</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('master.items.conversions.store', $item) }}" class="mb-3">
                    @csrf
                    <div class="form-row align-items-end">
                        <div class="col-md-3">
                            <label for="from_unit_id">Dari Satuan <span class="text-danger">*</span></label>
                            <select name="from_unit_id" id="from_unit_id" class="form-control @error('from_unit_id') is-invalid @enderror" required>
                                <option value="">— Pilih —</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('from_unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->symbol }})
                                </option>
                                @endforeach
                            </select>
                            @error('from_unit_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="to_unit_id">Ke Satuan <span class="text-danger">*</span></label>
                            <select name="to_unit_id" id="to_unit_id" class="form-control @error('to_unit_id') is-invalid @enderror" required>
                                <option value="">— Pilih —</option>
                                @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('to_unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }} ({{ $unit->symbol }})
                                </option>
                                @endforeach
                            </select>
                            @error('to_unit_id') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="conversion_factor">Faktor Konversi <span class="text-danger">*</span></label>
                            <input type="number" name="conversion_factor" id="conversion_factor" step="0.000001" min="0.000001"
                                   class="form-control @error('conversion_factor') is-invalid @enderror"
                                   value="{{ old('conversion_factor') }}" placeholder="mis. 1000" required>
                            @error('conversion_factor') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Tambah</button>
                        </div>
                    </div>
                </form>

                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Dari</th>
                            <th>Ke</th>
                            <th>Faktor</th>
                            <th style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($item->unitConversions as $conversion)
                        <tr>
                            <td>{{ $conversion->fromUnit?->name }} ({{ $conversion->fromUnit?->symbol }})</td>
                            <td>{{ $conversion->toUnit?->name }} ({{ $conversion->toUnit?->symbol }})</td>
                            <td>1 {{ $conversion->fromUnit?->symbol }} = {{ rtrim(rtrim(number_format((float) $conversion->conversion_factor, 6, '.', ','), '0'), '.') }} {{ $conversion->toUnit?->symbol }}</td>
                            <td>
                                <form method="POST" action="{{ route('master.items.conversions.destroy', [$item, $conversion]) }}" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus konversi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Belum ada konversi satuan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
