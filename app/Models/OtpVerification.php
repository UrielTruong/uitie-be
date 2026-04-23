<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpVerification extends Model
{
    protected $table = 'otp_verification';
    protected $primaryKey = 'otp_id';

    const TYPE_LOGIN = 'LOGIN';
    const TYPE_FORGOT_PASSWORD = 'FORGOT_PASSWORD';
    const TYPE_VERIFY_PHONE = 'VERIFY_PHONE';

    protected $fillable = [
        'user_id',
        'otp_code',
        'otp_type',
        'expired_at',
        'is_used'
    ];

    /**Cast - ép kiểu tự động cho các cột từ database sang kiểu dữ liệu của Laravel*/
    protected $casts = [
        'expired_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Quan hệ ngược lại với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Helper kiểm tra OTP còn hạn không
     */
    public function isValid(): bool
    {
        return !$this->is_used && $this->expired_at->isFuture();
    }
}
