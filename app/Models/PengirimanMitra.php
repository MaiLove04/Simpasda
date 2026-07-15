<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengirimanMitra extends Model
{
    protected $table = 'pengiriman_mitras';

    protected $fillable = [

        'kode_pengiriman',

        'mitra_id',

        'tanggal',

        'total',

        'status_pengiriman',

        'status_pembayaran',

        'metode_pembayaran',

        'bukti_transfer',

        'tanggal_pembayaran',

        'catatan_verifikasi',

        'keterangan'

    ];

    // RELASI ke Mitra
   public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }

    // RELASI ke Detail Pengiriman
    public function details()
    {
        return $this->hasMany(DetailPengirimanMitra::class, 'pengiriman_id');
    }
}