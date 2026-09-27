<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'location', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────
    public function racks(): HasMany
    {
        return $this->hasMany(Rack::class);
    }

    // ── Accessors ──────────────────────────────────────
    public function getRacksCountAttribute(): int
    {
        return $this->racks()->count();
    }
}
