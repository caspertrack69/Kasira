<?php

namespace App\Models\Concerns;

use App\Support\EntityContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToEntity
{
    public static function bootBelongsToEntity(): void
    {
        static::addGlobalScope('entity', function (Builder $builder): void {
            if (! app()->bound(EntityContext::class)) {
                return;
            }

            $context = app(EntityContext::class);
            if (! $context->hasEntity()) {
                return;
            }

            $builder->where($builder->qualifyColumn('entity_id'), $context->id());
        });

        static::creating(function ($model): void {
            if (! app()->bound(EntityContext::class)) {
                return;
            }

            $context = app(EntityContext::class);
            if ($context->hasEntity() && empty($model->entity_id)) {
                $model->entity_id = $context->id();
            }
        });
    }

    public function scopeForEntity(Builder $query, string $entityId): Builder
    {
        return $query->withoutGlobalScope('entity')->where('entity_id', $entityId);
    }
}
