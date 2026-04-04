<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEntity;
use App\Models\Concerns\HasUlidPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory;
    use HasUlidPrimaryKey;
    use BelongsToEntity;
    use SoftDeletes;

    protected $fillable = [
        'entity_id',
        'sku',
        'name',
        'description',
        'unit',
        'default_price',
        'is_taxable',
        'tax_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'default_price' => 'decimal:2',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
