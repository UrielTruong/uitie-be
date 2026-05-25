<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function getByPost(int $postId): JsonResponse
    {
        $comments = Comment::with('user')
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
            'content' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'post_id' => $request->post_id,
            'user_id' => $request->attributes->get('user_id'),
            'parent_comment_id' => $request->parent_comment_id,
            'content' => $request->content,
        ]);

        $comment->load('user');

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