<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntity;
use App\Models\Concerns\HasUlidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends Model
{
    use HasFactory;
    use HasUlidPrimaryKey;
    use BelongsToEntity;

    protected $fillable = [
        'entity_id',
        'name',
        'code',
        'type',
        'rate',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
