<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSampah extends Model
    {
        protected $fillable = [

        'bank_sampah_id',

        'nama',

        //'kode_icon',

        'harga_per_kg',

        //'poin_per_kg',

        'status',

    ];


    // relasi ke bank sampah
    public function bankSampah()
    {
        return $this->belongsTo(BankSampah::class);
    }


    // relasi ke setor sampah (opsional, nanti dipakai)
    public function setorSampahs()
    {
        return $this->hasMany(SetorSampah::class);
    }
}