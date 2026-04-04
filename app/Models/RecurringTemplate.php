<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntity;
use App\Models\Concerns\HasUlidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringTemplate extends Model
{
    use HasFactory;
    use HasUlidPrimaryKey;
    use BelongsToEntity;

    protected $fillable = [
        'entity_id',
        'customer_id',
        'name',
        'frequency',
        'start_date',
        'end_date',
        'occurrences_limit',
        'occurrences_count',
        'next_generate_date',
        'auto_send',
        'is_active',
        'template_data',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'next_generate_date' => 'date',
            'template_data' => 'array',
            'auto_send' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
