<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kompon extends Model
{
    protected $table = 'kompons';

    protected $fillable = ['mesin_id', 'nama_komponen', 'stok'];

    public function mesin(): BelongsTo
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }
}
