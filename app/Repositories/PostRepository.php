<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;

class PostRepository implements PostRepositoryInterface
{
    public function getFeed(int $perPage = 15)
    {
        return Post::with(['user', 'category', 'attachments'])
            ->where('visibility', Post::VISIBILITY_PUBLIC)
            ->where('status', Post::STATUS_ACCEPTED)
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id)
    {
        return Post::findOrFail($id);
    }

    public function create(array $data)
    {
        return Post::create($data);
    }

    public function update(int $id, array $data)
    {
        $post = $this->findById($id);
        $post->update($data);
        return $post;
    }

    public function delete(int $id)
    {
        $post = $this->findById($id);
        $post->delete(); // soft delete vì Post có SoftDeletes
    }
}
