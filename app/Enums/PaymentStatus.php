<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Reversed = 'reversed';
}
