@extends('layouts.app')

@section('title', 'Tambah Role — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Role</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Role</h3>
            </div>
            <form method="POST" action="{{ route('roles.store') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nama Role <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" placeholder="misal: warehouse_admin" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi</label>
                        <input type="text" name="description" id="description" class="form-control"
                               value="{{ old('description') }}" placeholder="Deskripsi singkat role">
                    </div>

                    <hr>
                    <h5>Permission</h5>
                    @foreach($permissions as $module => $perms)
                    <div class="mb-3">
                        <strong class="text-primary">{{ strtoupper($module) }}</strong>
                        <div class="row">
                            @foreach($perms as $perm)
                            <div class="col-md-4">
                                <div class="icheck-primary">
                                    <input type="checkbox" id="perm_{{ $perm->id }}" name="permissions[]" value="{{ $perm->id }}"
                                           {{ in_array($perm->id, old('permissions', [])) ? 'checked' : '' }}>
                                    <label for="perm_{{ $perm->id }}">{{ $perm->name }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    @error('permissions') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection