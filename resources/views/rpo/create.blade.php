@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Buat RPO Baru</h3>
    </div>
    <div class="card-body">
        {{-- Form akan di-isi oleh frontend --}}
        <p>Formulir buat RPO akan segera tersedia.</p>
        <a href="{{ route('rpo.index') }}" class="btn btn-secondary">Kembali ke Daftar RPO</a>
    </div>
</div>
@endsection