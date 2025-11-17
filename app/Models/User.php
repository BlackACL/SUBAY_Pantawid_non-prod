<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Password;

/**
 * Class User
 *
 * @mixin \Spatie\Permission\Traits\HasRoles
 * @method \Illuminate\Support\Collection getRoleNames()
 * @method \Spatie\Permission\Models\Role|\Spatie\Permission\Models\Permission assignRole(...$roles)
 * @method bool hasRole(string|array|\Spatie\Permission\Models\Role $roles)
 * @method bool hasAnyRole(string|array|\Spatie\Permission\Models\Role $roles)
 * @method bool hasAllRoles(array|\Spatie\Permission\Models\Role ...$roles)
 * @method \Illuminate\Support\Collection getPermissionNames()
 * @method bool hasPermissionTo(string|\Spatie\Permission\Models\Permission $permission, string|null $guardName = null)
 * @method $this givePermissionTo(...$permissions)
 * @method $this revokePermissionTo(...$permissions)
 * @method bool hasDirectPermission(string|\Spatie\Permission\Models\Permission $permission)
 * @mixin IdeHelperUser
 */


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fullname',
        'username',
        'company_id',
        'email',
        'password',
        'email_verified_at',
        'employee_status',
        'office',
        'region',
        'province',
        'municipality',
        'access_level',
        'activated',
        'locked_status',
        'archived_at',
        // Normalized foreign keys
        'place_id',
        'office_id',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];
     /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_expires_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'archived_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Two-Factor Code Handling
     */
    public function regenerateTwoFactorCode()
    {
        $this->timestamps = false;
        $this->two_factor_code = rand(100000, 999999);
        $this->two_factor_expires_at = now()->addMinutes(5);
        $this->save();
    }

    /**
     * 🔐 Lock/Unlock helpers (Logic 1)
     */
    public function isLocked(): bool
    {
        return strtoupper($this->locked_status) === 'YES';
    }

    public function lock(): void
    {
        $this->locked_status = 'Yes';
        $this->save();
    }

    public function unlockAndSendReset(): void
    {
        $this->locked_status = 'No';
        $this->save();

        // Send reset link to user
        Password::sendResetLink(['email' => $this->email]);
    }

    /**
     * 👤 Fullname Split/Compose (Logic 3)
     */
    public function getLastNameAttribute(): ?string
    {
        [$ln, $fn, $mn] = static::splitFullname($this->fullname);
        return $ln;
    }

    public function getFirstNameAttribute(): ?string
    {
        [$ln, $fn, $mn] = static::splitFullname($this->fullname);
        return $fn;
    }

    public function getMiddleNameAttribute(): ?string
    {
        [$ln, $fn, $mn] = static::splitFullname($this->fullname);
        return $mn;
    }

    public static function splitFullname(?string $fullname): array
    {
        if (!$fullname) return [null, null, null];

        $parts = explode(',', $fullname);
        $last = trim($parts[0] ?? '');
        $rhs  = trim($parts[1] ?? '');

        $rhsParts = preg_split('/\s+/', $rhs);
        $first = trim($rhsParts[0] ?? '');
        $middle = trim(implode(' ', array_slice($rhsParts, 1))) ?: '';

        return [$last ?: null, $first ?: null, $middle ?: null];
    }

    public static function composeFullname($last, $first, $middle = '')
    {
        $last = trim($last ?? '');
        $first = trim($first ?? '');
        $middle = trim($middle ?? '');
        $middlePart = $middle ? ' ' . $middle : '';
        // Format: LASTNAME , FIRSTNAME MIDDLENAME
        return ($last ? $last : '') . ($last ? ' , ' : '') . ($first ? $first : '') . $middlePart;
    }

    // optional: decomposeFullname returns array('first_name','middle_name','last_name')
    public static function decomposeFullname($fullname)
    {
        $fullname = trim($fullname ?? '');
        if (!$fullname) return ['first_name' => '', 'middle_name' => '', 'last_name' => ''];

        if (strpos($fullname, ',') !== false) {
            [$last, $rest] = array_map('trim', explode(',', $fullname, 2));
            $parts = preg_split('/\s+/', $rest);
            $first = $parts[0] ?? '';
            $middle = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
            return ['first_name' => $first, 'middle_name' => $middle, 'last_name' => $last];
        }
        // fallback: first middle last
        $parts = preg_split('/\s+/', $fullname);
        if (count($parts) === 1) return ['first_name' => $parts[0], 'middle_name' => '', 'last_name' => ''];
        if (count($parts) === 2) return ['first_name' => $parts[0], 'middle_name' => '', 'last_name' => $parts[1]];
        $first = array_shift($parts);
        $last = array_pop($parts);
        $middle = implode(' ', $parts);
        return ['first_name' => $first, 'middle_name' => $middle, 'last_name' => $last];
    }

    /**
     * 🔎 Scopes for filtering
     */
    public function scopeActive($q)
    {
        return $q->where('activated', 'Yes');
    }

    public function scopeArchived($q)
    {
        return $q->where('activated', 'No');
    }

    /**
     * 📦 Relationships for user history (Logic 10)
     * Linked by fullname = receiver
     */
    public function fets()
    {
        return $this->hasMany(\App\Models\FetsDocument::class, 'user_id', 'id');
    }

    public function inventoryItems()
    {
        return $this->hasMany(\App\Models\Inventory::class, 'RECEIVER', 'fullname');
    }

    public function units()
    {
        return $this->hasMany(\App\Models\Inventory::class, 'RECEIVER', 'fullname');
    }

    /**
     * 🌍 Normalized relationships (NEW)
     */
    public function place()
    {
        return $this->belongsTo(Place::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * 🔑 Role checks
     */
    public function isSuperAdmin() { return $this->hasRole('superadmin'); }
    public function isRegionalAdmin() { return $this->hasRole('Regional DPSC'); }
    public function isProvincialAdmin() { return $this->hasRole('Provincial DPSC'); }
    public function isEmployee() { return $this->hasRole('Employee'); }
}
