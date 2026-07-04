<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class JadwalPenjemputan extends Model
{


    public $incrementing = false;
    protected $keyType = 'string';
    
    use HasUuids;

    protected $fillable = [
        'bank_sampah_id',
        'nasabah_id',
        'kurir_id',
        'tanggal_penjemputan',
        'tanggal', // 🔥 Tambahkan ini agar bisa disimpan!
        'alamat',
        'catatan',
        'status',
    ];

    public function nasabah(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'nasabah_id'
        );
    }

    public function kurir(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'kurir_id'
        );
    }

    public function bankSampah(): BelongsTo
    {
        return $this->belongsTo(
            BankSampah::class
        );
    }
}
