<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSearchPostRequest;
use App\Http\Requests\Admin\ExportPostPdfRequest;
use App\Http\Resources\Admin\AdminPostResource;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostController extends Controller
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
    ) {}

    /**
     * API 1: Lấy danh sách bài viết (Đồng bộ với Route getListPost)
     * GET /api/admin/post
     */
    public function getListPost(AdminSearchPostRequest $request)
    {
        // Lấy các filter từ request
        $filters = $request->only(['keyword', 'category_id', 'status', 'visibility', 'user_id', 'from_date', 'to_date']);
        $perPage = $request->integer('per_page', 15);

        $posts = $this->postRepository->adminSearch($filters, $perPage);

        // Trả về Resource collection cho đồng bộ với phần Report/User
        return AdminPostResource::collection($posts);
    }

    /**
     * API 2: Duyệt hoặc Từ chối bài viết
     * PUT /api/admin/post/{id}/validate
     */
    public function validatePost(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:Accepted,Rejected,Pending',
            'reject_reason' => 'required_if:status,Rejected|string|max:500|nullable'
        ]);

        $post = $this->postRepository->findById($id);

        if (!$post) {
            return response()->json([
                'status'  => false,
                'message' => 'Không tìm thấy bài viết.',
            ], 404);
        }

        try {
            $updatedPost = $this->postRepository->update($id, [
                'status'        => $request->status,
                'reject_reason' => ($request->status === 'Rejected') ? $request->reject_reason : null,
                'updated_at'    => Carbon::now(),
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Cập nhật trạng thái bài viết thành công!',
                'data'    => new AdminPostResource($updatedPost),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xuất danh sách bài viết ra PDF
     * GET /api/admin/post/export-pdf
     */
    public function exportPdf(ExportPostPdfRequest $request): Response
    {
        $filters = $request->only(['keyword', 'category_id', 'status', 'visibility']);

        $posts = $this->postRepository->getAllForExport($filters);

        $stats = [
            'total'    => $posts->count(),
            'accepted' => $posts->where('status', Post::STATUS_ACCEPTED)->count(),
            'pending'  => $posts->where('status', Post::STATUS_PENDING)->count(),
            'rejected' => $posts->where('status', Post::STATUS_REJECTED)->count(),
            'public'   => $posts->where('visibility', Post::VISIBILITY_PUBLIC)->count(),
            'private'  => $posts->where('visibility', Post::VISIBILITY_PRIVATE)->count(),
        ];

        $pdf = Pdf::loadView('reports.posts-pdf', [
            'posts'       => $posts,
            'stats'       => $stats,
            'filters'     => $filters,
            'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
        ])
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = 'danh-sach-bai-viet-' . Carbon::now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}