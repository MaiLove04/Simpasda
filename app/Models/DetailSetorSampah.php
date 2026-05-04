<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailSetorSampah
    extends Model
{
    protected $fillable = [

        'setor_sampah_id',

        'jenis_sampah_id',

        'berat',

        'harga_per_kg',

        'subtotal',
    ];


    public function jenisSampah()
    {
        return $this->belongsTo(
            JenisSampah::class
        );
    }
}