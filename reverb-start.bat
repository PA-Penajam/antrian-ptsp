@echo off
title Laravel Reverb - antrian-ptsp
cd /d C:\pa-penajam\antrian-ptsp
echo Starting Laravel Reverb Server...
echo Working directory: %cd%
echo.
php artisan reverb:start
