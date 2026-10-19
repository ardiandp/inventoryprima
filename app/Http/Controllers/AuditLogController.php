<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Daftar audit log dengan filter.
     */
    public function index(Request $request): View
    {
        $filters = [
            'action' => $request->input('action'),
            'user_id' => $request->input('user_id'),
            'model_type' => $request->input('model_type'),
        ];

        $logs = AuditLog::with('user')
            ->when($filters['action'], fn ($q, $v) => $q->where('action', $v))
            ->when($filters['user_id'], fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['model_type'], fn ($q, $v) => $q->where('model_type', $v))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        // Daftar action unik untuk dropdown filter
        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('audit_logs.index', compact('logs', 'users', 'actions', 'filters'));
    }
}