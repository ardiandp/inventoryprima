@extends('layouts.app')

@section('title', 'Edit Proyek — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('master.projects.index') }}">Proyek</a></li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Edit Proyek: {{ $project->name }}</h3>
            </div>
            <form method="POST" action="{{ route('master.projects.update', $project) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="code">Kode <span class="text-danger">*</span></label>
                        <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror"
                               value="{{ old('code', $project->code) }}" maxlength="20" required>
                        @error('code') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="name">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $project->name) }}" maxlength="150" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $project->description) }}</textarea>
                        @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror"
                                       value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                                @error('start_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">Tanggal Selesai</label>
                                <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                       value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}">
                                @error('end_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="custom-control custom-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="custom-control-input" name="is_active" value="1" id="is_active"
                               {{ old('is_active', $project->is_active ? '1' : '0') === '1' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Aktif</label>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                    <a href="{{ route('master.projects.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection