<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComponentReplacement extends Model
{
    protected $fillable = [
        'component_id',
        'old_name',
        'new_name',
        'installed_at',
        'removed_at',
        'hm_installed',
        'hm_removed',
        'target_lifetime_hm',
        'target_lifetime_months',
        'installed_at_new',
        'hm_installed_new',
        'target_lifetime_hm_new',
        'target_lifetime_months_new',
        'reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'installed_at' => 'date',
            'removed_at' => 'date',
            'hm_installed' => 'decimal:2',
            'hm_removed' => 'decimal:2',
            'target_lifetime_hm' => 'decimal:2',
            'installed_at_new' => 'date',
            'hm_installed_new' => 'decimal:2',
            'target_lifetime_hm_new' => 'decimal:2',
        ];
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }
}
