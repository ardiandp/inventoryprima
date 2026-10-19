@extends('layouts.app')

@section('title', 'Beranda — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item active">Beranda</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Selamat datang di Aplikasi Inventori &amp; Pengadaan Barang</h3>
            </div>
            <div class="card-body">
                <p>Aplikasi web internal untuk mengintegrasikan permintaan barang (RPO), verifikasi Purchasing,
                approval Direktur, Purchase Order (PO), penerimaan, QC, dan pengendalian stok.</p>
                <hr>
                <p><strong>PT Prima Mitrainti Sejati</strong> — Sistem Informasi Pengadaan &amp; Inventori</p>
            </div>
        </div>
    </div>
</div>
@endsection