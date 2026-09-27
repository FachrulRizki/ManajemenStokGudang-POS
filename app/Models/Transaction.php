<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number', 'shift_id', 'user_id',
        'customer_name', 'customer_phone',
        'subtotal', 'discount_percent', 'discount_amount',
        'tax_percent', 'tax_amount', 'grand_total',
        'payment_method_id', 'amount_paid', 'change_amount', 'payment_reference',
        'status', 'notes', 'transaction_at',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'tax_percent'      => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'grand_total'      => 'decimal:2',
        'amount_paid'      => 'decimal:2',
        'change_amount'    => 'decimal:2',
        'transaction_at'   => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    // ── Accessors ──────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid'    => 'Lunas',
            'pending' => 'Menunggu',
            'voided'  => 'Dibatalkan',
            default   => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'paid'    => 'success',
            'pending' => 'warning',
            'voided'  => 'danger',
            default   => 'secondary',
        };
    }

    // ── Helpers ────────────────────────────────────────
    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $last = static::whereDate('created_at', today())->withTrashed()->count();
        return sprintf('INV-%s-%04d', $date, $last + 1);
    }
}
