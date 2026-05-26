<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterJadwalRutin extends Model
{
    use HasFactory;

    protected $fillable = [
        'nasabah_id',
        'kurir_id',
        'hari_penjemputan',
        'jam_estimasi',
        'is_aktif'
    ];

    // Relasi ke data Nasabah
    public function nasabah()
    {
        return $this->belongsTo(User::class, 'nasabah_id');
    }

    // Relasi ke data Kurir
    public function kurir()
    {
        return $this->belongsTo(User::class, 'kurir_id');
    }
}