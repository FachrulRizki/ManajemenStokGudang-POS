<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'description', 'icon',
        'requires_reference', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'requires_reference' => 'boolean',
        'is_active'          => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ── Accessors ──────────────────────────────────────
    public function getIconClassAttribute(): string
    {
        return $this->icon ?: match ($this->type) {
            'cash'    => 'fas fa-money-bill-wave',
            'digital' => 'fas fa-qrcode',
            'card'    => 'fas fa-credit-card',
            default   => 'fas fa-wallet',
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'cash'    => '#10b981',
            'digital' => '#6366f1',
            'card'    => '#3b82f6',
            default   => '#64748b',
        };
    }
}
