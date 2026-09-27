<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'create'  => 'Tambah',
            'update'  => 'Ubah',
            'delete'  => 'Hapus',
            'login'   => 'Login',
            'logout'  => 'Logout',
            'export'  => 'Export',
            'import'  => 'Import',
            'restore' => 'Restore',
            default   => ucfirst($this->action),
        };
    }

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'create'  => 'success',
            'update'  => 'warning',
            'delete'  => 'danger',
            'login'   => 'info',
            'logout'  => 'secondary',
            'export'  => 'primary',
            default   => 'secondary',
        };
    }

    // ── Static Helper ────────────────────────────────────
    public static function log(
        string $action,
        string $module,
        string $description,
        ?Model $model = null,
        array $oldValues = [],
        array $newValues = []
    ): void {
        static::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'model_type'  => $model ? get_class($model) : null,
            'model_id'    => $model?->id,
            'old_values'  => $oldValues ?: null,
            'new_values'  => $newValues ?: null,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}
