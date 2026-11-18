<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    protected $fillable = ['name', 'description'];

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }

    public function checkUps(): HasMany
    {
        return $this->hasMany(CheckUp::class);
    }
}
