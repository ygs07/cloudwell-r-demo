<?php

namespace App\Enums;

enum ReferralStatus: int
{
    case RECEIVED = 1;
    case TRIAGING = 2;
    case ACCEPTED = 3;
    case REJECTED = 4;
    case CANCELLED = 5;

    public function label(): string
    {
        return match ($this) {
            self::RECEIVED => 'Received',
            self::TRIAGING => 'Triaging',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
        };
    }
}
