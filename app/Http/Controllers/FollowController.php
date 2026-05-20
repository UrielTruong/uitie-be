<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\FollowRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    protected $followRepository;

    // Sử dụng Dependency Injection để nạp Interface vào theo đúng kiến trúc của nhóm
    public function __construct(FollowRepositoryInterface $followRepository)
    {
        $this->followRepository = $followRepository;
    }

    /**
     * API Xử lý bấm nút Theo dõi / Hủy theo dõi
     * POST /api/user/{id}/follow
     */
    public function toggle(Request $request, $id): JsonResponse
    {
        // Kiểm tra xem có tự follow chính mình không
        if ($request->user()->id == $id) {
            return response()->json([
                'status' => false,
                'error' => 'Bạn không thể tự theo dõi chính mình.'
            ], 400);
        }

        $result = $this->followRepository->toggleFollow($request->user()->id, $id);

        return response()->json([
            'status' => true,
            'message' => $result['message'],
            'is_following' => $result['is_following']
        ]);
    }

    /**
     * API Tải danh sách bài viết từ những người đang theo dõi kèm bộ lọc thời gian
     * GET /api/posts/feed/followers?time=all|today|week|month
     */
    public function followersFeed(Request $request): JsonResponse
    {
        $timeFilter = $request->query('time', 'all');
        $posts = $this->followRepository->getFollowersFeed($request->user()->id, $timeFilter);

        return response()->json([
            'status' => true,
            'posts' => $posts
        ]);
    }
}