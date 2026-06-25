<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarikTunai extends Model
{
    use HasFactory;

    protected $table = 'tarik_tunais';

    protected $fillable = [
        'user_id',
        'jumlah_nominal',
        'status',
        'tanggal_request',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_request' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    /**
     * Relasi ke User (Nasabah)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
