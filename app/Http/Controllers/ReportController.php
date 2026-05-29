<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportRepositoryInterface $reports
    ) {}

    /**
     * Sinh viên gửi báo cáo bài viết
     */
    public function reportPost(Request $request, $postId): JsonResponse
    {
        $post = Post::find($postId);
        if (!$post) {
            return response()->json(['status' => false, 'message' => 'Bài viết không tồn tại.'], 404);
        }

        $request->validate([
            'reason' => 'required|string|min:10'
        ], [
            'reason.required' => 'Vui lòng nhập lý do báo cáo.',
            'reason.min' => 'Nội dung báo cáo quá ngắn, vui lòng mô tả chi tiết hơn.'
        ]);

        $studentId = $request->attributes->get('user_id');

        $report = $this->reports->create([
            'reporter_id'      => $studentId,
            'reported_post_id' => $postId,
            'reported_user_id' => null,
            'target_type'      => Report::TARGET_POST,
            'reason'           => $request->reason,
            'status'           => Report::STATUS_PENDING,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Gửi báo cáo vi phạm thành công.',
            'data' => $report
        ], 201);
    }

    /**
     * Sinh viên gửi báo cáo người dùng (Tài khoản vi phạm)
     */
    public function reportUser(Request $request, $userId): JsonResponse
    {
        // 1. Kiểm tra tài khoản bị báo cáo có tồn tại không
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Người dùng không tồn tại.'], 404);
        }

        $studentId = $request->attributes->get('user_id');

        // 2. Không cho phép sinh viên tự báo cáo chính mình
        if ($studentId == $userId) {
            return response()->json(['status' => false, 'message' => 'Bạn không thể tự báo cáo tài khoản của chính mình.'], 400);
        }

        // 3. Validation nội dung báo cáo tương tự như Post
        $request->validate([
            'reason' => 'required|string|min:10'
        ], [
            'reason.required' => 'Vui lòng cung cấp lý do báo cáo tài khoản vi phạm.',
            'reason.min' => 'Nội dung báo cáo quá ngắn, vui lòng mô tả hành vi vi phạm chi tiết hơn.'
        ]);

        // 4. Ghi nhận vào Database thông qua Repository
        $report = $this->reports->create([
            'reporter_id'      => $studentId,
            'reported_post_id' => null, // Đặt NULL khi đối tượng là User để thỏa mãn ràng buộc DB
            'reported_user_id' => $userId,
            'target_type'      => Report::TARGET_USER,
            'reason'           => $request->reason,
            'status'           => Report::STATUS_PENDING,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Gửi báo cáo người dùng vi phạm thành công.',
            'data' => $report
        ], 201);
    }
}