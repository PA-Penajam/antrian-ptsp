<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Frontdesk = 'frontdesk';
    case Officer = 'officer';
    case Monitor = 'monitor';
}
