<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportPostPdfRequest as AdminExportPostPdfRequest;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\ExportPostPdfRequest;
use App\Http\Requests\GetListPostRequest;
use App\Http\Requests\SearchPostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Attachment;
use App\Models\Post;
use App\Models\Like;
use App\Repositories\Contracts\PostRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Services\AttachmentService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private AttachmentService $attachmentService,
    ) {}

    // GET /api/posts - Xem danh sách bài viết trên bảng tin (Khám phá / Đang theo dõi)
    public function getList(GetListPostRequest $request): PostCollection
    {
        $perPage = $request->integer('per_page', 15);
        $currentUserId = $request->attributes->get('user_id');

        // Verify user is authenticated - should be handled by middleware
        if (!$currentUserId) {
            abort(401, 'Unauthorized: user_id not found');
        }

        // Get scope from query params (default: 'all' for Explore tab)
        $scope = $request->query('scope', 'all');

        // HANDLE "FOLLOWING" TAB - Get posts only from users that current user follows
        if ($scope === 'following') {
            $followingIds = DB::table('follows')
                ->where('follower_id', $currentUserId)
                ->pluck('following_id');

            $posts = Post::with(['user', 'category', 'attachments'])
                ->whereIn('user_id', $followingIds)
                ->where('status', Post::STATUS_ACCEPTED)
                ->latest()
                ->paginate($perPage);
        }
        // HANDLE "EXPLORE" TAB (DEFAULT) - Get all accepted posts
        else {
            $posts = Post::with(['user', 'category', 'attachments'])
                ->where('status', Post::STATUS_ACCEPTED)
                ->latest()
                ->paginate($perPage);
        }

        // Load parent post data for Share UI
        if ($posts instanceof \Illuminate\Pagination\AbstractPaginator) {
            $posts->getCollection()->loadMissing(['parentPost.user', 'parentPost.attachments', 'parentPost.category']);
        } else {
            $posts->loadMissing(['parentPost.user', 'parentPost.attachments', 'parentPost.category']);
        }

        $this->attachPostStats($posts, $currentUserId);

        return new PostCollection($posts);
    }

    // Hàm tiện ích để đính kèm lượt Like, Share và Trạng thái Liked vào dữ liệu trả về
    private function attachPostStats($posts, $userId)
    {
        $isSingle = $posts instanceof Post;
        $collection = $isSingle ? collect([$posts]) : $posts;

        if ($collection->isEmpty()) return $posts;

        $postIds = $collection->pluck('id')->toArray();

        // Đếm tổng lượt like
        $likesCounts = Like::whereIn('post_id', $postIds)
            ->select('post_id', DB::raw('count(*) as count'))
            ->groupBy('post_id')
            ->pluck('count', 'post_id');

        // Check xem user đang login đã like các bài nào
        $userLikes = Like::whereIn('post_id', $postIds)
            ->where('user_id', $userId)
            ->pluck('post_id')
            ->toArray();

        // Đếm số lượt share
        $sharesCounts = Post::whereIn('parent_post_id', $postIds)
            ->select('parent_post_id', DB::raw('count(*) as count'))
            ->groupBy('parent_post_id')
            ->pluck('count', 'parent_post_id');

        foreach ($collection as $post) {
            $post->setAttribute('likes', $likesCounts[$post->id] ?? 0);
            $post->setAttribute('liked', in_array($post->id, $userLikes));
            $post->setAttribute('shares', $sharesCounts[$post->id] ?? 0);

            // Đảm bảo parentPost được đính kèm vào JSON trả về
            if ($post->relationLoaded('parentPost') && $post->parentPost) {
                $post->setAttribute('shared_post', $post->parentPost);
            }
        }

        return $posts;
    }

    // GET /api/posts/search - tìm kiếm bài viết
    public function search(SearchPostRequest $request): PostCollection
    {
        $filters = $request->only(['keyword', 'category_id']);
        $perPage = $request->integer('per_page', 15);
        $currentUserId = $request->attributes->get('user_id');

        $posts = $this->postRepository->adminSearch($filters, $perPage);

        if ($posts instanceof \Illuminate\Pagination\AbstractPaginator) {
            $posts->getCollection()->loadMissing(['parentPost.user', 'parentPost.attachments', 'parentPost.category']);
        } else {
            $posts->loadMissing(['parentPost.user', 'parentPost.attachments', 'parentPost.category']);
        }

        $this->attachPostStats($posts, $currentUserId);

        return new PostCollection($posts);
    }

    // POST /api/posts - Tạo bài viết mới
    public function create(CreatePostRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $post = $this->postRepository->create([
                'user_id'     => $request->attributes->get('user_id'),
                'content'     => $request->content,
                'visibility'  => $request->visibility ?? Post::VISIBILITY_PUBLIC,
                'category_id' => $request->category_id,
                'status'      => Post::STATUS_PENDING,
            ]);

            if ($request->filled('attachments')) {
                $attachmentIds = collect($request->attachments)->map(function ($item) {
                    return Attachment::create([
                        'file_url'  => $item['file_url'],
                        'file_type' => $item['file_type'],
                        'file_name' => Cache::pull("fname:{$item['file_url']}"),  // pull = get + delete
                    ])->id;
                })->all();

                $post->attachments()->attach($attachmentIds);
            }

            DB::commit();

            $post->load('user', 'category', 'attachments');
            $this->attachPostStats($post, $request->attributes->get('user_id'));

            return response()->json([
                'status'  => true,
                'message' => 'Post created successfully',
                'data'    => new PostResource($post),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create post: ' . $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/posts/{id} - Chỉnh sửa bài viết
    public function update(UpdatePostRequest $request, int $id): JsonResponse
    {
        $post = $this->postRepository->findById($id);

        if (!$post) {
            return response()->json([
                'status'  => false,
                'message' => 'Post not found',
            ], 404);
        }

        if ((string) $post->user_id !== (string) $request->attributes->get('user_id')) {
            return response()->json([
                'status'  => false,
                'message' => 'Forbidden: you do not own this post',
            ], 403);
        }

        DB::beginTransaction();

        try {
            $updated = $this->postRepository->update($id, [
                'content'     => $request->content ?? $post->content,
                'category_id' => $request->category_id ?? $post->category_id,
                'visibility'  => $request->visibility ?? $post->visibility,
                'updated_at'  => Carbon::now(),
                'is_edited'   => true,
            ]);

            if ($request->filled('attachments')) {
                // Detach tất cả attachment cũ
                $updated->attachments()->detach();

                $attachmentIds = collect($request->attachments)->map(function ($item) {
                    return Attachment::create([
                        'file_url'  => $item['file_url'],
                        'file_type' => $item['file_type'],
                        'file_name' => Cache::pull("fname:{$item['file_url']}"),  // pull = get + delete
                    ])->id;
                })->all();

                $updated->attachments()->attach($attachmentIds);
            }

            DB::commit();

            $updated->load('user', 'category', 'attachments');
            $this->attachPostStats($updated, $request->attributes->get('user_id'));

            return response()->json([
                'status'  => true,
                'message' => 'Post updated successfully',
                'data'    => new PostResource($updated),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update post: ' . $e->getMessage(),
            ], 500);
        }
    }

    // DELETE /api/posts/{id} - Xóa bài viết
    public function destroy(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        $post = $this->postRepository->findById($id);

        if (!$post) {
            return response()->json([
                'status'  => false,
                'message' => 'Post not found',
            ], 404);
        }

        // Chỉ người tạo hoặc Admin mới được xóa
        $isOwner = (string) $post->user_id === (string) $request->attributes->get('user_id');
        $isAdmin = in_array($request->attributes->get('user_role'), ['Admin', 'Super Admin']);

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

    // GET /api/users/{id}/posts - Lấy danh sách bài viết của 1 user
    public function getUserPosts(int $id, \Illuminate\Http\Request $request): PostCollection
    {
        $perPage = $request->integer('per_page', 15);
        $currentUserId = $request->attributes->get('user_id');

        $query = Post::with(['user', 'category', 'attachments', 'parentPost.user', 'parentPost.attachments', 'parentPost.category'])
            ->where('user_id', $id);

        // Chỉ chủ sở hữu bài viết mới xem được bài viết private hoặc pending
        if ((string) $id !== (string) $currentUserId) {
            $query->where('visibility', Post::VISIBILITY_PUBLIC)
                ->where('status', Post::STATUS_ACCEPTED);
        }

        $posts = $query->latest()->paginate($perPage);
        $this->attachPostStats($posts, $currentUserId);

        return new PostCollection($posts);
    }

    // POST /api/posts/{id}/like - Toggle Like bài viết
    public function toggleLike(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        $userId = $request->attributes->get('user_id');
        $post = $this->postRepository->findById($id);

        if (!$post) {
            return response()->json([
                'status'  => false,
                'message' => 'Post not found',
            ], 404);
        }

        // Use firstOrCreate to prevent race condition
        $like = Like::firstOrCreate(
            ['user_id' => $userId, 'post_id' => $id],
            ['user_id' => $userId, 'post_id' => $id]
        );

        if ($like->wasRecentlyCreated) {
            // Like was just created
            $liked = true;
        } else {
            // Like already existed, so delete it
            $like->delete();
            $liked = false;
        }

        $likesCount = Like::where('post_id', $id)->count();

        return response()->json([
            'status'  => true,
            'message' => $liked ? 'Liked post' : 'Unliked post',
            'data'    => [
                'liked' => $liked,
                'likes' => $likesCount,
            ],
        ]);
    }

    // POST /api/posts/{id}/share - Share bài viết
    public function share(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        $userId = $request->attributes->get('user_id');
        $post = $this->postRepository->findById($id);

        if (!$post) {
            return response()->json([
                'status'  => false,
                'message' => 'Post not found',
            ], 404);
        }

        // Validate content is provided and is a string
        $content = $request->input('content', '');
        if (!is_string($content)) {
            return response()->json([
                'status'  => false,
                'message' => 'Content must be a string',
            ], 422);
        }

        // Tạo một bài viết mới (share) trỏ đến parent_post_id
        $newPost = $this->postRepository->create([
            'user_id'        => $userId,
            'parent_post_id' => $id,
            'category_id'    => $post->category_id,
            'content'        => $content,
            'visibility'     => Post::VISIBILITY_PUBLIC,
            'status'         => Post::STATUS_ACCEPTED,
        ]);

        $sharesCount = Post::where('parent_post_id', $id)->count();

        return response()->json([
            'status'  => true,
            'message' => 'Post shared successfully',
            'data'    => [
                'shares' => $sharesCount,
            ],
        ]);
    }
}
