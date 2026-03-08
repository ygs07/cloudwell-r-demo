<?php

namespace App\Enums;

enum Genotype: int
{
    case AA = 1;
    case AS = 2;
    case SS = 3;
    case AC = 4;
    case SC = 5;
    case CC = 6;

    public function label(): string
    {
        return match ($this) {
            self::AA => 'AA',
            self:: AS => 'AS',
            self::SS => 'SS',
            self::AC => 'AC',
            self::SC => 'SC',
            self::CC => 'CC',
        };
    }
}