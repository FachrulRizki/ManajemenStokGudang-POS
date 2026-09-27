<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOutType extends Model
{
    protected $fillable = [
        'code', 'name', 'color', 'icon', 'affects_stock', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'affects_stock' => 'boolean',
        'is_active'     => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────
    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class);
    }

    // ── Accessors ──────────────────────────────────────
    public function getBadgeHtmlAttribute(): string
    {
        $icon = $this->icon ? '<i class="' . $this->icon . '"></i> ' : '';
        return '<span class="badge badge-' . $this->color . '">' . $icon . $this->name . '</span>';
    }
}
