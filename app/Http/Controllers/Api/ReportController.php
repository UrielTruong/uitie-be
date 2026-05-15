<?php

namespace App\Http\Controllers\Api; 

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Report;
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
        'reporter_id'      => $studentId, // Sử dụng biến vừa lấy
        'reported_post_id' => $postId,
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
}