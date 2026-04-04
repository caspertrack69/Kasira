<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'entity_id',
        'gateway',
        'event_id',
        'signature',
        'payload',
        'status',
        'processed_at',
        'error_message',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
