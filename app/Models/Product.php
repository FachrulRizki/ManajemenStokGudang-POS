<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'barcode',
        'category_id',
        'unit_id',
        'supplier_id',
        'description',
        'image',
        'purchase_price',
        'selling_price',
        'stock',
        'min_stock',
        'rack_location',
        'rack_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price'  => 'decimal:2',
            'is_active'      => 'boolean',
        ];
    }

    // ── Relasi ──────────────────────────────────────────
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }

    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class);
    }

    // Relasi baru: rak dan transaksi POS
    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    // ── Helper ──────────────────────────────────────────
    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'out';
        }
        if ($this->stock <= $this->min_stock) {
            return 'low';
        }
        return 'normal';
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return asset('images/no-image.png');
    }

    public function getTotalStockValueAttribute(): float
    {
        return $this->stock * $this->purchase_price;
    }

    // Total quantity sold in a given month (via stock_outs + transactions)
    public function monthlySoldQty(int $month = null, int $year = null): int
    {
        $month = $month ?? now()->month;
        $year  = $year  ?? now()->year;

        // Stock out manual (non-POS)
        $manualOut = $this->stockOuts()
            ->where('type', 'sale')
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->sum('quantity');

        // Penjualan via POS
        $posOut = $this->transactionItems()
            ->whereHas('transaction', fn ($q) => $q
                ->where('status', 'paid')
                ->whereMonth('transaction_at', $month)
                ->whereYear('transaction_at', $year)
            )
            ->sum('quantity');

        return $manualOut + $posOut;
    }
}
