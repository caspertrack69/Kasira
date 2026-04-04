<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUlids;

trait HasUlidPrimaryKey
{
    use HasUlids;

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
