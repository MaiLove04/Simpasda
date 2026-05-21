<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetorSampah extends Model
{
    use HasFactory;

    // Nama tabel kamu (pastikan sesuai, jika jamak pakai 'setor_sampahs')
    protected $table = 'setor_sampahs'; 

    protected $fillable = [
        'user_id',
        'kurir_id',
        'jenis_sampah_id',
        'catatan',
        'berat',
        'harga_per_kg',
        'total',
        'foto_sampah',
        'status'
    ];

    // ========================================================
    // TAMBAHKAN RELASI BERIKUT DI DALAM MODEL KAMU
    // ========================================================

    /**
     * Relasi ke tabel Users (Nasabah)
     */
    public function user()
    {
        // Menghubungkan kolom user_id ke primary key 'id' di tabel users
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke tabel Jenis Sampah
     */
    public function jenisSampah()
    {
        // Menghubungkan kolom jenis_sampah_id ke primary key 'id' di tabel jenis_sampahs
        return $this->belongsTo(JenisSampah::class, 'jenis_sampah_id');
    }
}