<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceStatusHistory extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'entity_id',
        'invoice_id',
        'from_status',
        'to_status',
        'changed_by',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
