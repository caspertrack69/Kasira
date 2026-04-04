<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntity;
use App\Models\Concerns\HasUlidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;
    use HasUlidPrimaryKey;
    use BelongsToEntity;

    protected $fillable = [
        'entity_id',
        'customer_id',
        'payment_method_id',
        'payment_number',
        'amount',
        'payment_date',
        'reference',
        'status',
        'notes',
        'proof_path',
        'gateway_response',
        'confirmed_by',
        'confirmed_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'gateway_response' => 'array',
            'confirmed_at' => 'datetime',
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

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}
