<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; 

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==========================================
// JADWAL OTOMATIS GENERATE JADWAL HARIAN
// Berjalan setiap hari jam 21:00 WIB untuk generate jadwal besok
// ==========================================
Schedule::command('jadwal:generate-harian')->dailyAt('21:00');

// ⚠️ JANGAN UNCOMMENT DI PRODUCTION — Hanya untuk testing manual di terminal:
// php artisan jadwal:generate-harian --today
// php artisan jadwal:generate-harian
// Schedule::command('jadwal:generate-rutin')->everyMinute();
