<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(Request $request, int $id): JsonResponse
    {
        $currentUserId = $request->attributes->get('user_id');

        if ((int) $currentUserId === $id) {
            return response()->json([
                'status'  => false,
                'message' => 'Không thể tự theo dõi chính mình.',
            ], 422);
        }

        if (!User::where('id', $id)->where('status', User::STATUS_ACTIVE)->exists()) {
            return response()->json([
                'status'  => false,
                'message' => 'Người dùng không tồn tại.',
            ], 404);
        }

        Follow::firstOrCreate([
            'follower_id'  => $currentUserId,
            'following_id' => $id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Theo dõi thành công.',
        ]);
    }

    public function unfollow(Request $request, int $id): JsonResponse
    {
        $currentUserId = $request->attributes->get('user_id');

        Follow::where('follower_id', $currentUserId)
            ->where('following_id', $id)
            ->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Bỏ theo dõi thành công.',
        ]);
    }
}
