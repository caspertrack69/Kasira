<?php

namespace App\Models;

use App\Models\Concerns\HasUlidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entity extends Model
{
    use HasFactory;
    use HasUlidPrimaryKey;

    protected $fillable = [
        'name',
        'code',
        'legal_name',
        'tax_id',
        'address',
        'city',
        'province',
        'postal_code',
        'country',
        'phone',
        'email',
        'logo_path',
        'currency',
        'invoice_prefix',
        'default_payment_terms',
        'default_tax_rate',
        'reminder_days',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'default_tax_rate' => 'decimal:2',
            'reminder_days' => 'array',
            'metadata' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'entity_users')
            ->using(EntityUser::class)
            ->withPivot(['role', 'assigned_by'])
            ->withTimestamps();
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
