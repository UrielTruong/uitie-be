<?php

namespace App\Repositories;

use App\Repositories\Contracts\FollowRepositoryInterface;
use App\Models\User;
use App\Models\Post;
use Carbon\Carbon;

class FollowRepository implements FollowRepositoryInterface
{
    public function toggleFollow(int $currentUserId, int $targetUserId): array
    {
        $user = User::findOrFail($currentUserId);
        
        // Toggle: Nếu chưa follow thì thêm bản ghi, nếu đã follow rồi thì xóa bản ghi
        $status = $user->following()->toggle($targetUserId);
        $isFollowing = count($status['attached']) > 0;

        return [
            'is_following' => $isFollowing,
            'message' => $isFollowing ? 'Đã theo dõi thành công' : 'Đã hủy theo dõi thành công'
        ];
    }

    public function getFollowersFeed(int $currentUserId, string $timeFilter)
    {
        $user = User::findOrFail($currentUserId);
        $followingIds = $user->following()->pluck('following_id');

        $query = Post::query()->with('user')
                     ->whereIn('user_id', $followingIds)
                     ->where('status', 'Accepted') // Chỉ lấy bài viết được duyệt
                     ->whereNull('deleted_at');

        if ($timeFilter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($timeFilter === 'week') {
            $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($timeFilter === 'month') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }

        return $query->latest()->get();
    }
}