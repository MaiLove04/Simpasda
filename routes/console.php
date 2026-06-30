<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; 

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==========================================
// CARA MENULIS JADWAL OTOMATIS DI LARAVEL 11
// ==========================================
Schedule::command('jadwal:generate-harian')->dailyAt('21:00');

// JADWAL RUTIN BANK SAMPAH ASRI (INTERVAL 2 HARI SEKALI JAM 06:00 SUBOH)
// Schedule::command('jadwal:generate-rutin')->cron('0 6 */2 * *');

//kalo mau langsung coba bisa uncomment dibawah ini
Schedule::command('jadwal:generate-rutin')->everyMinute();