<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    protected $table = 'mitras';

    protected $fillable = [
        'user_id',
        'nama_mitra',
        'jenis_mitra',
        'penanggung_jawab',
        'no_hp',
        'email',
        'alamat',
        'status',
        'keterangan'
    ];

    public function pengiriman()
    {
        return $this->hasMany(PengirimanMitra::class, 'mitra_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}