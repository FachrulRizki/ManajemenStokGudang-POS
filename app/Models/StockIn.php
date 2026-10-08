<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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

    // ── Auto reference number (atomic via MySQL advisory lock) ───
    public static function generateReferenceNumber(): string
    {
        $prefix  = 'SI-' . date('Ymd');
        $lockKey = 'stock_in_ref_' . date('Ymd');

        // Gunakan MySQL GET_LOCK agar hanya satu proses yang generate nomor di satu waktu
        DB::statement("SELECT GET_LOCK('{$lockKey}', 10)");

        try {
            // Hitung total baris hari ini (termasuk soft-deleted) untuk sequence
            $count = DB::table('stock_ins')
                ->where('reference_number', 'like', $prefix . '-%')
                ->count();

            $seq = $count + 1;
            $ref = $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // Jika nomor itu sudah ada (karena ada gap dari delete), naikkan terus sampai unik
            while (
                DB::table('stock_ins')
                    ->where('reference_number', $ref)
                    ->exists()
            ) {
                $seq++;
                $ref = $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            }

            return $ref;
        } finally {
            DB::statement("SELECT RELEASE_LOCK('{$lockKey}')");
        }
    }
}
