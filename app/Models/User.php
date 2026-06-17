<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\JadwalPenjemputan;


class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'alamat',
        'foto',
        'no_hp',
        'role',
        'status',
        'bank_sampah_id',
        'kode_nasabah',
        'pin_hash',
        'pin_attempts',
        'pin_locked_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function bankSampah()
    {
        return $this->belongsTo(\App\Models\BankSampah::class);
    }

    // Relasi dengan JadwalPenjemputan sebagai nasabah
    public function jadwalNasabah(): HasMany
    {
        return $this->hasMany(
            JadwalPenjemputan::class,
            'nasabah_id'
        );
    }

    public function jadwalKurir(): HasMany
    {
        return $this->hasMany(
            JadwalPenjemputan::class,
            'kurir_id'
        );
    }
}
