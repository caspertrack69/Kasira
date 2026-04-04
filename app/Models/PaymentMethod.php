<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntity;
use App\Models\Concerns\HasUlidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasFactory;
    use HasUlidPrimaryKey;
    use BelongsToEntity;

    protected $fillable = [
        'entity_id',
        'name',
        'type',
        'account_name',
        'account_number',
        'provider',
        'instructions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }
}
