<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aduan extends Model
{
    use HasFactory;

    protected $table = 'aduans';

    protected $fillable = [
        'user_id',
        'role_pengirim',
        'kategori_aduan',
        'isi_aduan',
        'foto_bukti',
        'status',
        'tanggapan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}