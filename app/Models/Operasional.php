<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operasional extends Model
{
    protected $table = 'operasional';

    protected $fillable = [

        'tanggal',

        'jenis_transaksi',

        'kategori',

        'harga',

        'jumlah',

        'total',

        'keterangan',

        'sumber',
        
        'kode_referensi',

    ];
}