<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SetorSampah extends Model
{
    use HasFactory;
    protected $table = 'setor_sampahs'; 

    public function nasabah()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jenis_sampah()
    {
        return $this->belongsTo(JenisSampah::class, 'jenis_sampah_id');
    }
}