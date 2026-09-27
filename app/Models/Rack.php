<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rack extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'warehouse_id', 'code', 'name', 'row', 'column', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // ── Accessors ──────────────────────────────────────
    /** Label lengkap: "Gudang Utama - Rak A (Baris 1)" */
    public function getFullLabelAttribute(): string
    {
        $label = $this->warehouse->name . ' › ' . $this->name;
        if ($this->row || $this->column) {
            $label .= ' (' . implode('-', array_filter([$this->row, $this->column])) . ')';
        }
        return $label;
    }

    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    public function getTotalStockAttribute(): int
    {
        return $this->products()->sum('stock');
    }
}
