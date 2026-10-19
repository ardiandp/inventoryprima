<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Simpan audit log.
     *
     * @param  string  $action  Aksi: login, logout, create, update, delete, approve, reject, dll.
     * @param  string|null  $modelType  FQCN model yang terpengaruh.
     * @param  int|string|null  $modelId  ID model yang terpengaruh.
     * @param  array|null  $changes  ['old' => [...], 'new' => [...]] atau ['field' => value].
     * @param  array|null  $context  Data tambahan (mis. reference_type, reference_id).
     */
    public static function log(
        string $action,
        ?string $modelType = null,
        ?int|string|null $modelId = null,
        ?array $changes = null,
        ?array $context = null,
    ): AuditLog {
        $user = Auth::user();

        $oldValues = $changes['old'] ?? null;
        $newValues = $changes['new'] ?? ($changes ?? null);

        // Jika changes bukan struktur old/new, gunakan seluruhnya sebagai new_values.
        if ($changes !== null && ! array_key_exists('old', $changes) && ! array_key_exists('new', $changes)) {
            $oldValues = null;
            $newValues = $changes;
        }

        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId !== null ? (string) $modelId : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'reference_type' => $context['reference_type'] ?? null,
            'reference_id' => isset($context['reference_id']) ? (string) $context['reference_id'] : null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}