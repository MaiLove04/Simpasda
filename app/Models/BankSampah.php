<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankSampah extends Model
{
    protected $fillable = [
        'nama_bank',
        'alamat',
        'status'
    ];

    public function users()
    {
        return $this->hasMany(
            User::class,
            'bank_sampah_id'
        );
    }
}