<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Staff = 'staff';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
