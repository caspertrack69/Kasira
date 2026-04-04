<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntity;
use App\Models\Concerns\HasUlidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use HasUlidPrimaryKey;
    use BelongsToEntity;
    use SoftDeletes;

    protected $fillable = [
        'entity_id',
        'customer_number',
        'name',
        'email',
        'phone',
        'tax_id',
        'billing_address',
        'shipping_address',
        'credit_balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'credit_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function recurringTemplates(): HasMany
    {
        return $this->hasMany(RecurringTemplate::class);
    }
}
