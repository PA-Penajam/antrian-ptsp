<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class InstitutionController extends Controller
{
    /**
     * Display the institution settings.
     */
    public function index()
    {
        return response()->json([
            // Matches Institution interface
            'name' => 'Pengadilan Agama Penajam',
            'address' => 'Jl. Propinsi Km. 9, Nipah-Nipah, Penajam',
            'phone' => '(0542) 8530321',
            'email' => 'pa.penajam@gmail.com',
            'operating_hours' => 'Senin - Kamis: 08:00 - 16:30, Jumat: 08:00 - 17:00',
            'logo_path' => null,
        ]);
    }
}
