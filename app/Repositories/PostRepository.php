<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

    public function adminSearch(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Post::with(['user', 'category', 'attachments']);

        // Không có where visibility/status — admin thấy tất cả bài kể cả Private, Pending, Rejected

        if (!empty($filters['keyword'])) {
            $query->where('content', 'like', "%{$filters['keyword']}%");
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Admin-only filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['visibility'])) {
            $query->where('visibility', $filters['visibility']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function getAllForExport(array $filters = []): Collection
    {
        $query = Post::with(['user', 'category']);

        if (!empty($filters['keyword'])) {
            $query->where('content', 'like', "%{$filters['keyword']}%");
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['visibility'])) {
            $query->where('visibility', $filters['visibility']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function countPostsByCategory()
    {
        return Post::select('categories.category_name', DB::raw('COUNT(*) as total'))
            ->join('categories', 'posts.category_id', '=', 'categories.id')
            ->groupBy('categories.category_name')
            ->get();
    }

    public function countPosts()
    {
        return Post::where('status', Post::STATUS_PENDING)->count();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Post::latest()->paginate($perPage);
    }
}
