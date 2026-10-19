@extends('layouts.app')

@section('title', 'Semua Permission — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Role</a></li>
<li class="breadcrumb-item active">Permission</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Semua Permission</h3>
            </div>
            <div class="card-body">
                @foreach($permissions as $module => $perms)
                <div class="mb-4">
                    <h5 class="text-primary"><i class="fas fa-folder-open mr-1"></i> {{ strtoupper($module) }}</h5>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th style="width:250px">Nama</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($perms as $perm)
                            <tr>
                                <td><code>{{ $perm->name }}</code></td>
                                <td>{{ $perm->description ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection