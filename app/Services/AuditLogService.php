<?php

namespace App\Services;

use App\Jobs\SaveAuditLogJob;

class AuditLogService
{
    /**
     * Ghi nhật ký thao tác người dùng vào bảng audit_logs (bất đồng bộ qua Queue).
     *
     * @param  string  $action  Tên hành động, VD: 'class_created', 'attendance_check_in'
     * @param  array{
     *     user_id?: int|null,
     *     class_id?: string|null,
     *     table_name?: string|null,
     *     row_id?: int|null,
     *     old_values?: array|null,
     *     new_values?: array|null,
     * }  $options
     */
    public function log(string $action, array $options = []): void
    {
        SaveAuditLogJob::dispatch([
            'user_id'    => $options['user_id']  ?? auth()->id(),
            'class_id'   => $options['class_id'] ?? null,
            'action'     => $action,
            'table_name' => $options['table_name'] ?? null,
            'row_id'     => $options['row_id']     ?? null,
            'old_values' => isset($options['old_values']) ? json_encode($options['old_values'], JSON_UNESCAPED_UNICODE) : null,
            'new_values' => isset($options['new_values']) ? json_encode($options['new_values'], JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => substr(request()->ip() ?? '', 0, 45),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
