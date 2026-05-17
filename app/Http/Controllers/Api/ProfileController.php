<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * GET /api/user/profile
     */
    public function getProfile(Request $request): JsonResponse
    {
        // Lấy user_id từ tham số query (để xem hồ sơ người khác), nếu không có thì lấy của người đang đăng nhập
        $targetUserId = $request->query('user_id');
        $userIdToFetch = $targetUserId ? $targetUserId : $request->attributes->get('user_id');
        
        $user = User::find($userIdToFetch);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Không tìm thấy thông tin người dùng.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $user,
        ]);
    }

    /**
     * PUT /api/user/profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        // Lấy user_id từ request attributes (do JWT Middleware của bạn thiết lập)
        $userId = $request->attributes->get('user_id');
        
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Không tìm thấy thông tin người dùng.',
            ], 404);
        }

        // Cập nhật thông tin dựa trên dữ liệu đã được validate
        $user->update($request->validated());

        return response()->json([
            'status'  => true,
            'message' => 'Cập nhật hồ sơ thành công!',
            'data'    => $user,
        ]);
    }
}
