<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    public function getByPost(int $postId): JsonResponse
    {
        $comments = Comment::with(['user', 'attachments'])
            ->where('post_id', $postId)
            ->oldest() // Bình luận cũ xếp trước
            ->get();

        return response()->json([
            'status' => true,
            'data' => CommentResource::collection($comments)
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'parent_comment_id' => 'nullable|exists:comments,id',
            'content' => 'required_without:attachments|nullable|string|max:1000',
            'attachments' => 'nullable|array',
            'attachments.*.file_url' => 'required_with:attachments|string',
            'attachments.*.file_type' => 'required_with:attachments|string',
        ]);

        DB::beginTransaction();

        try {
            $comment = Comment::create([
                'post_id' => $request->post_id,
                'user_id' => $request->attributes->get('user_id'),
                'parent_comment_id' => $request->parent_comment_id,
                'content' => $request->content,
            ]);

            if ($request->filled('attachments')) {
                $attachmentIds = collect($request->attachments)->map(function ($item) {
                    return Attachment::create([
                        'file_url'  => $item['file_url'],
                        'file_type' => $item['file_type'],
                        'file_name' => Cache::pull("fname:{$item['file_url']}"),
                    ])->id;
                })->all();

                $comment->attachments()->attach($attachmentIds);
            }

            DB::commit();
            $comment->load(['user', 'attachments']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Lỗi khi tạo bình luận: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Bình luận thành công',
            'data' => new CommentResource($comment)
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $comment = Comment::findOrFail($id);

        $userId = $request->attributes->get('user_id');
        $userRole = $request->attributes->get('user_role');

        if ((string)$comment->user_id !== (string)$userId && !in_array($userRole, ['Admin', 'Super Admin'])) {
            return response()->json([
                'status' => false,
                'message' => 'Không có quyền xóa bình luận này'
            ], 403);
        }

        $comment->delete(); // LƯU Ý: Phải thiết lập onDelete('cascade') ở migration để tự động xóa comment con

        return response()->json([
            'status' => true,
            'message' => 'Đã xóa bình luận'
        ]);
    }
}