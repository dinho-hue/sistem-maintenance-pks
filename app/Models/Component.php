<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Component extends Model
{
    protected $fillable = [
        'machine_id',
        'name',
        'installed_at',
        'hm_installed',
        'target_lifetime_hm',
        'target_lifetime_months',
    ];

    protected function casts(): array
    {
        return [
            'installed_at' => 'date',
            'hm_installed' => 'decimal:2',
            'target_lifetime_hm' => 'decimal:2',
        ];
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function replacements(): HasMany
    {
        return $this->hasMany(ComponentReplacement::class);
    }

    public function getCurrentHmAttribute(): float
    {
        return (float) ($this->machine->latestHmLog?->hm_reading ?? $this->machine->hm_awal);
    }

    public function getHmUsageAttribute(): ?float
    {
        if ($this->hm_installed === null) {
            return null;
        }

        return max(0, $this->current_hm - (float) $this->hm_installed);
    }

    public function getHmRemainingAttribute(): ?float
    {
        if ($this->target_lifetime_hm === null || $this->hm_usage === null) {
            return null;
        }

        return max(0, (float) $this->target_lifetime_hm - $this->hm_usage);
    }

    public function getHmUsagePercentAttribute(): ?int
    {
        if ((float) $this->target_lifetime_hm <= 0 || $this->hm_usage === null) {
            return null;
        }

        return min(100, (int) round($this->hm_usage / (float) $this->target_lifetime_hm * 100));
    }

    public function getCalendarUsagePercentAttribute(): ?int
    {
        if ($this->installed_at === null || (int) $this->target_lifetime_months <= 0) {
            return null;
        }

        $deadline = $this->installed_at->copy()->addMonths((int) $this->target_lifetime_months);
        $totalDays = max(1, $this->installed_at->diffInDays($deadline));
        $usedDays = max(0, $this->installed_at->diffInDays(today()));

        return min(100, (int) round($usedDays / $totalDays * 100));
    }

    public function getCalendarMonthsRemainingAttribute(): ?int
    {
        if ($this->installed_at === null || (int) $this->target_lifetime_months <= 0) {
            return null;
        }

        $deadline = $this->installed_at->copy()->addMonths((int) $this->target_lifetime_months);
        if (today()->greaterThanOrEqualTo($deadline)) {
            return 0;
        }

        return (int) today()->diffInMonths($deadline);
    }

    public function getStatusAttribute(): string
    {
        $percentages = array_filter([
            $this->hm_usage_percent,
            $this->calendar_usage_percent,
        ], fn ($percentage) => $percentage !== null);

        if ($percentages === []) {
            return 'Belum diatur';
        }
        if (max($percentages) >= 100) {
            return 'Lewat Batas';
        }
        if (max($percentages) >= 80) {
            return 'Mendekati Batas';
        }

        return 'Normal';
    }
}
