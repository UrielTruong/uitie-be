<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Post::with(['user', 'category', 'attachments'])
            ->where('visibility', Post::VISIBILITY_PUBLIC)
            ->where('status', Post::STATUS_ACCEPTED);

        // Tìm theo nội dung (LIKE) - search contains
        if (!empty($filters['keyword'])) {
            $query->where('content', 'like', "%{$filters['keyword']}%");
        }

        // Lọc theo danh mục / chủ đề search exact
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->latest()->paginate($perPage);
    }
}
