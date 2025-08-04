<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

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
        'employee_status',
        'office',
        'region',
        'province',
        'municipality',
        'access_level',
        'activated',
        'locked_status',
        'deleted_status',
        'archived_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
        'password' => 'hashed',
        'archived_at' => 'datetime',
    ];


    public function regenerateTwoFactorCode()
    {
        $this->timestamps = false;
        $this->two_factor_code = rand(100000, 999999);
        $this->two_factor_expires_at = now()->addMinutes(5);
        $this->save();
    }

    // Role relationships and methods

    public function isSuperAdmin()
    {
        return $this->hasRole('superadmin');
    }

    public function isRegionalAdmin()
    {
        return $this->hasRole('Regional DPSC');
    }

    public function isProvincialAdmin()
    {
        return $this->hasRole('Provincial DPSC');
    }

    public function isEmployee()
    {
        return $this->hasRole('Employee');
    }

    public function isArchived()
    {
        return $this->deleted_status === 'Yes';
    }

    public function archive()
    {
        $this->update([
            'deleted_status' => 'Yes',
            'archived_at' => now(),
        ]);
    }

    public function unarchive()
    {
        $this->update([
            'deleted_status' => 'No',
            'archived_at' => null,
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('deleted_status', 'No');
    }

    public function scopeArchived($query)
    {
        return $query->where('deleted_status', 'Yes');
    }
}
