<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSearchPostRequest;
use App\Http\Resources\Admin\AdminPostResource;
use App\Http\Resources\PostCollection;
use App\Repositories\Contracts\PostRepositoryInterface;

class PostController extends Controller
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
    ) {}

    // GET /api/admin/posts/search
    public function searchPost(AdminSearchPostRequest $request)
    {
        $filters = $request->only(['keyword', 'category_id', 'status', 'visibility', 'user_id', 'from_date', 'to_date']);
        $perPage = $request->integer('per_page', 15);

        $posts = $this->postRepository->adminSearch($filters, $perPage);

        return AdminPostResource::collection($posts);
    }
}
