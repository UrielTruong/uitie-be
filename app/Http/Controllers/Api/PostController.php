<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportPostPdfRequest as AdminExportPostPdfRequest;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\ExportPostPdfRequest;
use App\Http\Requests\GetListPostRequest;
use App\Http\Requests\SearchPostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PostController extends Controller
{
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {}

    // GET /api/posts - Xem danh sách bài viết trên bảng tin
    public function getList(GetListPostRequest $request): PostCollection
    {
        $perPage = $request->integer('per_page', 15);
        $posts = $this->postRepository->getFeed($perPage);

        return new PostCollection($posts);
    }

    // GET /api/posts/search - tìm kiếm bài viết
    public function search(SearchPostRequest $request): PostCollection
    {
        $filters = $request->only(['keyword', 'category_id']);
        $perPage = $request->integer('per_page', 15);

        $posts = $this->postRepository->adminSearch($filters, $perPage);

        return new PostCollection($posts);
    }

    // POST /api/posts - Tạo bài viết mới
    public function create(CreatePostRequest $request): JsonResponse
    {
        $post = $this->postRepository->create([
            'user_id'     => $request->user_id,
            'content'     => $request->content,
            'visibility'  => $request->visibility ?? Post::VISIBILITY_PUBLIC,
            'category_id' => $request->category_id,
            'status'      => Post::STATUS_PENDING,
        ]);

        $post->load('user', 'category');

        return response()->json([
            'status'  => true,
            'message' => 'Post created successfully',
            'data'    => new PostResource($post),
        ], 201);
    }

    // PUT /api/posts/{id} - Chỉnh sửa bài viết
    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        $post = $this->postRepository->findById($id);

        // Chỉ người tạo mới được sửa

        if ((string) $post->user_id !== (string) $request->user_id) {
            return response()->json([
                'status'  => false,
                'message' => 'Forbidden: you do not own this post',
            ], 403);
        }

        $updated = $this->postRepository->update($id, [
            'content'    => $request->content ?? $post->content,
            'visibility' => $request->visibility ?? $post->visibility,
            'is_edited'  => true,
        ]);

        $updated->load('user', 'category');

        return response()->json([
            'status'  => true,
            'message' => 'Post updated successfully',
            'data'    => new PostResource($updated),
        ]);
    }

    // DELETE /api/posts/{id} - Xóa bài viết
    public function destroy(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        $post = $this->postRepository->findById($id);

        // Chỉ người tạo hoặc Admin mới được xóa
        $isOwner = (string) $post->user_id === (string) $request->user_id;
        $isAdmin = in_array($request->user_role, ['Admin', 'Super Admin']);

        if (!$isOwner && !$isAdmin) {
            return response()->json([
                'status'  => false,
                'message' => 'Forbidden: you cannot delete this post',
            ], 403);
        }

        $this->postRepository->delete($id);

        return response()->json([
            'status'  => true,
            'message' => 'Post deleted successfully',
        ]);
    }
}
