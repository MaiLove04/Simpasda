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
        'tipe_jadwal',
        'hari_penjemputan',
        'interval_hari',
        'tanggal_mulai',
        'jam_estimasi',
        'is_aktif'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'is_aktif' => 'boolean',
    ];

    public function nasabah()
    {
        return $this->belongsTo(User::class, 'nasabah_id');
    }

    public function kurir()
    {
        return $this->belongsTo(User::class, 'kurir_id');
    }
}