<?php

namespace App\Enums;

/**
 * Konstanta session key untuk modul non-auth (kiosk, tv display).
 */
enum ModuleSession: string
{
    case KioskAuthenticated = 'kiosk_authenticated';
    case KioskAuthenticatedAt = 'kiosk_authenticated_at';
    case TvDisplayAuthenticated = 'tv_display_authenticated';
    case TvDisplayAuthenticatedAt = 'tv_display_authenticated_at';
}
