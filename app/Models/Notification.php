<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// WARNING: Table 'notifications' conflicts with Laravel's Notifiable trait on User model.
// See progress.md for fix options.
class Notification extends Model
{
    const TYPE_POST_APPROVED = 'POST_APPROVED';
    const TYPE_POST_REJECTED = 'POST_REJECTED';
    const TYPE_NEW_LIKE      = 'NEW_LIKE';
    const TYPE_NEW_COMMENT   = 'NEW_COMMENT';
    const TYPE_NEW_FOLLOWER  = 'NEW_FOLLOWER';
    const TYPE_GROUP_INVITE  = 'GROUP_INVITE';
    const TYPE_SYSTEM_ALERT  = 'SYSTEM_ALERT';

    protected $fillable = [
        'user_id',
        'content',
        'type',
        'is_read',
        'reference_id',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
