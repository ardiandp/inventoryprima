@extends('layouts.app')

@section('title', 'Audit Log — Inventori &amp; Pengadaan')

@section('breadcrumb')
<li class="breadcrumb-item active">Audit Log</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Audit Log</h3>
                <div class="card-tools">
                    <form method="GET" action="{{ route('audit-logs.index') }}" class="form-inline">
                        <select name="action" class="form-control form-control-sm mr-2">
                            <option value="">— Semua Aksi —</option>
                            @foreach($actions as $a)
                                <option value="{{ $a }}" {{ $filters['action'] == $a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                        <select name="user_id" class="form-control form-control-sm mr-2">
                            <option value="">— Semua Pengguna —</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ $filters['user_id'] == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-filter"></i> Filter</button>
                    </form>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap table-sm">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Pengguna</th>
                            <th>Aksi</th>
                            <th>Model</th>
                            <th>Detail</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $log->user?->name ?? 'System' }}</td>
                            <td>
                                @php
                                    $badge = match(true) {
                                        $log->action === 'login', $log->action === 'logout' => 'info',
                                        $log->action === 'create' => 'success',
                                        $log->action === 'update' => 'warning',
                                        $log->action === 'delete' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge badge-{{ $badge }}">{{ $log->action }}</span>
                            </td>
                            <td><small>{{ class_basename($log->model_type ?? '-') }}</small></td>
                            <td>
                                @if($log->new_values)
                                    <small>
                                        @foreach(array_slice($log->new_values, 0, 3, true) as $k => $v)
                                            <span class="text-muted">{{ $k }}:</span> {{ is_scalar($v) ? \Illuminate\Support\Str::limit((string)$v, 40) : json_encode($v) }}<br>
                                        @endforeach
                                    </small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><small>{{ $log->ip_address }}</small></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada audit log.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection