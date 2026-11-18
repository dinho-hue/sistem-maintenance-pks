<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Machine extends Model
{
    protected $fillable = [
        'station_id',
        'name',
        'pic',
        'installation_date',
        'brand_type',
        'hm_awal',
    ];

    protected function casts(): array
    {
        return [
            'installation_date' => 'date',
            'hm_awal' => 'decimal:2',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function hmLogs(): HasMany
    {
        return $this->hasMany(HmLog::class);
    }

    public function checkUps(): HasMany
    {
        return $this->hasMany(CheckUp::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }

    public function latestHmLog(): HasOne
    {
        return $this->hasOne(HmLog::class)->latestOfMany('date');
    }
}
