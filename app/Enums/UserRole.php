<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Frontdesk = 'frontdesk';
    case Officer = 'officer';
    case Monitor = 'monitor';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Frontdesk => 'Frontdesk',
            self::Officer => 'Officer',
            self::Monitor => 'Monitor',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Admin => 'red',
            self::Frontdesk => 'blue',
            self::Officer => 'green',
            self::Monitor => 'purple',
        };
    }
}
