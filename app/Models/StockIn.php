<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockIn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference_number',
        'product_id',
        'supplier_id',
        'user_id',
        'quantity',
        'purchase_price',
        'total_price',
        'transaction_date',
        'invoice_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'purchase_price'   => 'decimal:2',
            'total_price'      => 'decimal:2',
        ];
    }

    // ── Relasi ──────────────────────────────────────────
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Auto reference number ────────────────────────────
    public static function generateReferenceNumber(): string
    {
        $prefix = 'SI-' . date('Ymd');

        // Gunakan lockForUpdate agar tidak ada dua request yang generate nomor sama
        // (harus dipanggil di dalam DB::transaction)
        $last = static::withTrashed()
            ->where('reference_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        $seq = $last ? (int) substr($last->reference_number, -4) + 1 : 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
