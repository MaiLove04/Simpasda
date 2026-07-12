<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    protected $fillable = [
        'user_id',
        'judul',
        'pesan',
        'type',
        'is_read',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
