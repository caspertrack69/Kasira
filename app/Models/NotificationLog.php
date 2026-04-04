<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'entity_id',
        'notifiable_type',
        'notifiable_id',
        'subject_type',
        'subject_id',
        'channel',
        'event_type',
        'recipient',
        'status',
        'failed_reason',
        'sent_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
