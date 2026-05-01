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

    // --- DANH SÁCH LÝ DO KHÓA TÀI KHOẢN ---
    const BLOCK_REASONS = [
        'SPAM'          => 'Tài khoản đăng bài quảng cáo hoặc spam liên tục.',
        'VIOLATION'     => 'Vi phạm nghiêm trọng quy định cộng đồng UITie.',
        'INAPPROPRIATE' => 'Sử dụng ngôn từ hoặc hình ảnh không phù hợp.',
        'REPORTED'      => 'Bị nhiều người dùng báo cáo vi phạm.',
        'OTHER'         => 'Lý do khác (Vui lòng ghi chú thêm).',
    ];

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
