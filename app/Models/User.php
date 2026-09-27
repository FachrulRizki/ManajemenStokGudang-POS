<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'avatar',
        'role',
        'is_active',
        'last_login_at',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // Relasi
    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }

    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Ambil shift aktif user saat ini
    public function activeShift(): ?Shift
    {
        return $this->shifts()->where('status', 'open')->latest()->first();
    }

    public function hasOpenShift(): bool
    {
        return $this->shifts()->where('status', 'open')->exists();
    }

    // Helper
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isKasir(): bool
    {
        return in_array($this->role, ['kasir', 'staff', 'admin', 'manager']);
    }

    public function canAccessPOS(): bool
    {
        return $this->is_active;
    }

    // ── Permission system ─────────────────────────────
    /** Override permission per user (pivot: granted true/false) */
    public function permissionOverrides(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot('granted');
    }

    /** Cache permission names untuk user ini */
    protected ?array $_cachedPermissions = null;

    /** Ambil semua permission efektif user (role default + override) */
    public function effectivePermissions(): array
    {
        if ($this->_cachedPermissions !== null) {
            return $this->_cachedPermissions;
        }

        // Mulai dari default role
        $defaults = Permission::defaultForRole($this->role);
        $granted  = array_flip($defaults); // name => true

        // Terapkan override per user
        foreach ($this->permissionOverrides()->get() as $perm) {
            if ($perm->pivot->granted) {
                $granted[$perm->name] = true;
            } else {
                unset($granted[$perm->name]);
            }
        }

        $this->_cachedPermissions = array_keys($granted);
        return $this->_cachedPermissions;
    }

    /** Cek apakah user punya permission tertentu */
    public function hasPermission(string $permission): bool
    {
        // Admin selalu punya semua akses
        if ($this->isAdmin()) return true;
        return in_array($permission, $this->effectivePermissions());
    }

    /** Cek beberapa permission sekaligus (salah satu cukup) */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isAdmin()) return true;
        $effective = $this->effectivePermissions();
        foreach ($permissions as $p) {
            if (in_array($p, $effective)) return true;
        }
        return false;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin'   => 'Administrator',
            'manager' => 'Manager',
            'staff'   => 'Staff',
            default   => 'Unknown',
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=6366f1&color=fff&size=128";
    }
}
