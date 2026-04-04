<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntity;
use App\Models\Concerns\HasUlidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditNote extends Model
{
    use HasFactory;
    use HasUlidPrimaryKey;
    use BelongsToEntity;

    protected $fillable = [
        'entity_id',
        'invoice_id',
        'customer_id',
        'credit_note_number',
        'amount',
        'reason',
        'status',
        'pdf_path',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
