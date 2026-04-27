<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Attachment extends Model
{
    const TYPE_IMAGE    = 'Image';
    const TYPE_VIDEO    = 'Video';
    const TYPE_DOCUMENT = 'Document';

    protected $fillable = [
        'file_url',
        'file_type',
    ];

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_attachments');
    }

    public function messages(): BelongsToMany
    {
        return $this->belongsToMany(Message::class, 'message_attachments');
    }

    public function comments(): BelongsToMany
    {
        return $this->belongsToMany(Comment::class, 'comment_attachments');
    }
}
