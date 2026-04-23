<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    const STATUS_PENDING   = 'Pending';
    const STATUS_RESOLVED  = 'Resolved';
    const STATUS_DISMISSED = 'Dismissed';

    const TARGET_USER = 'User';
    const TARGET_POST = 'Post';

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'reported_post_id',
        'resolved_by',
        'reason',
        'status',
        'target_type',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reportedPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'reported_post_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
