<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPengirimanMitra extends Model
{
    protected $table = 'detail_pengiriman_mitras';

    protected $fillable = [
        'pengiriman_id',
        'jenis_sampah',
        'berat',
        'harga',
        'subtotal'
    ];

    // RELASI ke Pengiriman
    public function pengiriman()
    {
        return $this->belongsTo(PengirimanMitra::class, 'pengiriman_id');
    }
}