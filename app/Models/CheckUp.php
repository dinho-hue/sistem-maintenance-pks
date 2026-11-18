<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckUp extends Model
{
    protected $table = 'check_ups';

    protected $fillable = [
        'station_id',
        'machine_id',
        'nama_teknisi',
        'tanggal',
        'keterangan',
        'hm_perbaikan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'hm_perbaikan' => 'decimal:2',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function legacyStation(): BelongsTo
    {
        return $this->belongsTo(Stasiun::class, 'stasiun_id');
    }

    public function legacyMachine(): BelongsTo
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }
}
