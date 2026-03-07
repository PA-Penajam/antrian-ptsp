<?php

namespace App\Enums;

enum QueueStatus: string
{
    case Booked = 'booked';
    case Waiting = 'waiting';
    case Called = 'called';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Booked => 'Terbooking',
            self::Waiting => 'Menunggu',
            self::Called => 'Dipanggil',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::Skipped => 'Dilewati',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Booked => 'blue',
            self::Waiting => 'orange',
            self::Called => 'purple',
            self::Completed => 'green',
            self::Cancelled => 'red',
            self::Skipped => 'gray',
        };
    }
}
