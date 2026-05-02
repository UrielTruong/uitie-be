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
        $posts = Post::where('status', 'Pending')
            ->with(['user:id,full_name,mssv']) // Chỉ lấy các cột cần thiết của user cho nhẹ
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
        $request->validate([
            'status' => 'required|in:Accepted,Rejected',
            'reject_reason' => 'required_if:status,Rejected|string|max:500|nullable'
        ], [
            'reject_reason.required_if' => 'Vui lòng cung cấp lý do để sinh viên biết tại sao bài viết bị từ chối.'
        ]);

        $post = Post::findOrFail($id);
        
        $post->status = $request->status;
        
        // Cập nhật lý do từ chối: Nếu Accepted thì xóa sạch lý do cũ để dữ liệu sạch
        $post->reject_reason = ($request->status === 'Rejected') 
            ? $request->reject_reason 
            : null;

        $post->save();

        return response()->json([
            'success' => true,
            'message' => ($request->status === 'Accepted') 
                ? 'Bài viết đã được duyệt thành công!' 
                : 'Bài viết đã bị từ chối.',
            'data' => $post
        ]);
    }

    /**
     * 3. Xóa bài viết vi phạm (Admin có quyền xóa trực tiếp)
     */
    public function deletePost($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa bài viết khỏi hệ thống.'
        ]);
    }
}