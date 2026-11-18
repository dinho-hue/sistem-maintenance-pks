<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HmLog extends Model
{
    protected $fillable = [
        'machine_id',
        'date',
        'hm_reading',
        'hm_terpakai',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'hm_reading' => 'decimal:2',
            'hm_terpakai' => 'decimal:2',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
