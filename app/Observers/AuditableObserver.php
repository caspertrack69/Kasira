<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Support\EntityContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class AuditableObserver
{
    public function created(Model $model): void
    {
        $this->writeLog($model, 'created', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->writeLog($model, 'updated', $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->writeLog($model, 'deleted', $model->getOriginal(), null);
    }

    private function writeLog(Model $model, string $event, ?array $oldValues, ?array $newValues): void
    {
        if ($model instanceof AuditLog) {
            return;
        }

        $request = request();
        $entityId = $model->getAttribute('entity_id') ?: app(EntityContext::class)->id();

        AuditLog::query()->create([
            'entity_id' => $entityId,
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => (string) $model->getKey(),
            'old_values' => $this->sanitizeValues($oldValues),
            'new_values' => $this->sanitizeValues($newValues),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    private function sanitizeValues(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return Arr::except($values, ['updated_at']);
    }
}
