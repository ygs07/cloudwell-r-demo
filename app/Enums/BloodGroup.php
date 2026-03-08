<?php

namespace App\Enums;

enum BloodGroup: int
{
    case A_POSITIVE = 1;
    case A_NEGATIVE = 2;
    case B_POSITIVE = 3;
    case B_NEGATIVE = 4;
    case AB_POSITIVE = 5;
    case AB_NEGATIVE = 6;
    case O_POSITIVE = 7;
    case O_NEGATIVE = 8;

    public function label(): string
    {
        return match ($this) {
            self::A_POSITIVE => 'A+',
            self::A_NEGATIVE => 'A-',
            self::B_POSITIVE => 'B+',
            self::B_NEGATIVE => 'B-',
            self::AB_POSITIVE => 'AB+',
            self::AB_NEGATIVE => 'AB-',
            self::O_POSITIVE => 'O+',
            self::O_NEGATIVE => 'O-',
        };
    }
}