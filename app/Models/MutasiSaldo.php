<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiSaldo extends Model
{
    use HasFactory;

    protected $table = 'mutasi_saldos';
    protected $guarded = ['id'];

    /**
     * RELASI: Mutasi ini milik siapa (Nasabah)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}