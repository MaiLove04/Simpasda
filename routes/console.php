<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // <-- Tambah import ini

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ==========================================
// CARA MENULIS JADWAL OTOMATIS DI LARAVEL 11
// ==========================================
Schedule::command('jadwal:generate-harian')->dailyAt('21:00');