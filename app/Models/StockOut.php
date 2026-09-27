<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOut extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number',
        'product_id',
        'user_id',
        'quantity',
        'selling_price',
        'total_price',
        'transaction_date',
        'customer_name',
        'type',
        'stock_out_type_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'selling_price'    => 'decimal:2',
            'total_price'      => 'decimal:2',
        ];
    }

    // ── Relasi ──────────────────────────────────────────
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function stockOutType(): BelongsTo
    {
        return $this->belongsTo(StockOutType::class);
    }

    // ── Auto reference number ────────────────────────────
    public static function generateReferenceNumber(): string
    {
        $prefix = 'SO-' . date('Ymd');
        $last   = static::where('reference_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $seq = $last ? (int) substr($last->reference_number, -4) + 1 : 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'sale'    => 'Penjualan',
            'return'  => 'Retur',
            'damaged' => 'Rusak/Hilang',
            'other'   => 'Lainnya',
            default   => '-',
        };
    }
}
