<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stasiun extends Model
{
    protected $table = 'stasiuns';

    protected $fillable = ['nama_stasiun', 'lokasi'];

    public function mesins(): HasMany
    {
        return $this->hasMany(Mesin::class, 'stasiun_id');
    }

    public function checkUps(): HasMany
    {
        return $this->hasMany(CheckUp::class, 'stasiun_id');
    }
}
