<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SetorSampah extends Model
{
    use HasFactory;

    protected $table = 'setor_sampahs';

    protected $guarded = ['id'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    /**
     * RELASI: Menghubungkan ke Pemilik Sampah (Nasabah)
     */
    public function nasabah()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * RELASI: Menghubungkan ke Kurir Lapangan yang menimbang
     */
    public function kurir()
    {
        return $this->belongsTo(User::class, 'kurir_id');
    }

    /**
     * 🔥 RELASI UTAMA MULTI-ITEM: Menghubungkan Ke Banyak Detail Rincian Sampah
     */
    public function details()
    {
        return $this->hasMany(DetailSetorSampah::class, 'setor_sampah_id');
    }

    /**
     * 🔒 FALLBACK RELASI LAMA: Dijaga agar data lama/single-item tidak error di web admin
     */
    public function jenis_sampah()
    {
        return $this->belongsTo(JenisSampah::class, 'jenis_sampah_id');
    }
}