<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * 1. Lấy danh sách các bài viết đang chờ duyệt (Pending)
     */
    public function getPendingPosts()
    {
        // Lấy bài viết có trạng thái Pending, kèm thông tin người đăng (user)
        $posts = Post::where('status', 'Pending')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * 2. Duyệt (Accepted) hoặc Từ chối (Rejected) bài viết
     */
    public function approvePost(Request $request, $id)
    {
        // Kiểm tra dữ liệu đầu vào đúng chuẩn nhóm quy định
        $request->validate([
            'status' => 'required|in:Accepted,Rejected',
            'reject_reason' => 'required_if:status,Rejected|string|nullable'
        ], [
            'reject_reason.required_if' => 'Vui lòng nhập lý do khi từ chối bài viết.'
        ]);

        $post = Post::findOrFail($id);
        
        // Cập nhật trạng thái mới
        $post->status = $request->status;
        
        // Nếu bị từ chối thì lưu lý do, nếu được duyệt thì xóa lý do cũ (nếu có)
        $post->reject_reason = ($request->status === 'Rejected') 
            ? $request->reject_reason 
            : null;

        $post->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái bài viết thành công!',
            'data' => $post
        ]);
    }
}