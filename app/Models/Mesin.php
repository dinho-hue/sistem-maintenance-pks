<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesin extends Model
{
    protected $table = 'mesins';

    protected $fillable = ['stasiun_id', 'nama_mesin', 'spesifikasi'];

    public function stasiun(): BelongsTo
    {
        return $this->belongsTo(Stasiun::class, 'stasiun_id');
    }

    public function kompons(): HasMany
    {
        return $this->hasMany(Kompon::class, 'mesin_id');
    }

    public function checkUps(): HasMany
    {
        return $this->hasMany(CheckUp::class, 'mesin_id');
    }
}
