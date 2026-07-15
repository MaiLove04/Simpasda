<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'user_id',
        'jenis_sampah',
        'catatan',
        'status'
    ];
}