<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailSetorSampah extends Model
{
    protected $fillable = ['setor_sampah_id', 'jenis_sampah_id', 'berat', 'harga_per_kg', 'total_harga'];
    public function jenisSampah()
    {
        return $this->belongsTo(JenisSampah::class, 'jenis_sampah_id');
    }

}