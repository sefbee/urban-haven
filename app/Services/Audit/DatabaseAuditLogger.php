<?php

namespace App\Services\Audit;

use App\Contracts\AuditLogger;
use App\Models\AuditLog;

class DatabaseAuditLogger implements AuditLogger
{
    public function record(
        ?int $actorId,
        string $action,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $ip = null,
    ): void {
        AuditLog::query()->create([
            'actor_id' => $actorId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip' => $ip,
            'created_at' => now(),
        ]);
    }
}
