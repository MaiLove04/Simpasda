<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetorSampah extends Model
{
    protected $fillable = [

        'user_id',

        'kurir_id',

        'jenis_sampah_id',

        'catatan',

        'berat',

        'harga_per_kg',

        'total',

        'status',
    ];


    public function jenisSampah()
    {
        return $this->belongsTo(
            JenisSampah::class
        );
    }

    public function details()
    {
        return $this->hasMany(
            DetailSetorSampah::class
        );
    }
}