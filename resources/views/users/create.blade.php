@extends('layouts.app')

@section('title', 'Tambah Pengguna — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('users.index') }}">Pengguna</a></li>
<li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Form Tambah Pengguna</h3>
            </div>
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name') }}" required>
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}" required>
                        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="department_id">Departemen</label>
                        <select name="department_id" id="department_id" class="form-control">
                            <option value="">— Pilih Departemen —</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="role">Role <span class="text-danger">*</span></label>
                        <select name="role" id="role" class="form-control @error('role') is-invalid @enderror" required>
                            <option value="">— Pilih Role —</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}" {{ old('role') == $r->name ? 'selected' : '' }}>
                                    {{ $r->name }} — {{ $r->description }}
                                </option>
                            @endforeach
                        </select>
                        @error('role') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection