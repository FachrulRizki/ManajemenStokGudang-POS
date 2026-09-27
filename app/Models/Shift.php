<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'shift_number', 'user_id', 'opened_at', 'closed_at',
        'opening_cash', 'closing_cash', 'expected_cash', 'cash_difference',
        'total_transactions', 'total_sales', 'total_discount',
        'total_cash', 'total_non_cash', 'status', 'notes',
    ];

    protected $casts = [
        'opened_at'         => 'datetime',
        'closed_at'         => 'datetime',
        'opening_cash'      => 'decimal:2',
        'closing_cash'      => 'decimal:2',
        'expected_cash'     => 'decimal:2',
        'cash_difference'   => 'decimal:2',
        'total_sales'       => 'decimal:2',
        'total_discount'    => 'decimal:2',
        'total_cash'        => 'decimal:2',
        'total_non_cash'    => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ── Accessors ──────────────────────────────────────
    public function getIsOpenAttribute(): bool
    {
        return $this->status === 'open';
    }

    public function getDurationAttribute(): string
    {
        $end   = $this->closed_at ?? now();
        $start = $this->opened_at;
        $hours = $start->diffInHours($end);
        $mins  = $start->diffInMinutes($end) % 60;
        return sprintf('%dj %dm', $hours, $mins);
    }

    // ── Helpers ────────────────────────────────────────
    public static function generateShiftNumber(): string
    {
        $date  = now()->format('Ymd');
        $last  = static::whereDate('created_at', today())->count();
        return sprintf('SHF-%s-%04d', $date, $last + 1);
    }

    /** Ambil shift aktif milik user, atau null */
    public static function activeForUser(int $userId): ?static
    {
        return static::where('user_id', $userId)->where('status', 'open')->latest()->first();
    }

    /** Hitung ulang ringkasan shift */
    public function recalculate(): void
    {
        $txns = $this->transactions()->where('status', 'paid');

        $this->total_transactions = $txns->count();
        $this->total_sales        = $txns->sum('grand_total');
        $this->total_discount     = $txns->sum('discount_amount');

        // Cash vs non-cash
        $cashMethodIds = PaymentMethod::where('type', 'cash')->pluck('id');
        $this->total_cash     = $txns->whereIn('payment_method_id', $cashMethodIds)->sum('grand_total');
        $this->total_non_cash = $this->total_sales - $this->total_cash;

        // Expected cash = opening + total cash received
        $this->expected_cash  = $this->opening_cash + $this->total_cash;

        if ($this->closing_cash !== null) {
            $this->cash_difference = $this->closing_cash - $this->expected_cash;
        }

        $this->save();
    }
}
