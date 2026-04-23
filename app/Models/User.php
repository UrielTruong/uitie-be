<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    /**
     * Không cần $primaryKey nữa vì mặc định đã là 'id'
     */

    // Constants để quản lý dữ liệu nhất quán
    const ROLE_SUPER_ADMIN = 'Super Admin';
    const ROLE_ADMIN       = 'Admin';
    const ROLE_STUDENT     = 'Student';

    const STATUS_INACTIVE  = 'Inactive';
    const STATUS_ACTIVE    = 'Active';
    const STATUS_LOCKED    = 'Locked';

    protected $fillable = [
        'email',
        'password',
        'full_name',
        'mssv',
        'phone_number',
        'role',
        'status',
        'status_reason',
        'faculty',
        'class_name',
        'academic_year',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }
}
