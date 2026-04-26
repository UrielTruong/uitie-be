<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 1. Lấy danh sách người dùng (Admin xem)
     * Phân nhóm theo khoa, lớp, khóa đào tạo
     */
    public function index(Request $request)
    {
        // Lấy danh sách kèm phân trang 10 người/trang [cite: 666]
        $users = User::query()
            ->when($request->faculty, function ($query, $faculty) {
                return $query->where('faculty', $faculty);
            })
            ->when($request->class_name, function ($query, $className) {
                return $query->where('class_name', $className);
            })
            ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * 2. Khóa hoặc Mở khóa tài khoản người dùng [cite: 156, 666]
     */
    public function updateStatus(Request $request, $id)
    {
        // Kiểm tra tính hợp lệ của dữ liệu đầu vào [cite: 342, 353]
        $request->validate([
            'status' => 'required|in:Active,Locked',
            'reason' => 'required_if:status,Locked|string|nullable' 
        ], [
            'reason.required_if' => 'Vui lòng nhập lý do khi khóa tài khoản.'
        ]);

        $user = User::findOrFail($id);
        
        // Cập nhật trạng thái 
        $user->status = $request->status; 
        
        // Cập nhật lý do: Nếu Locked thì lưu lý do, nếu Active thì xóa lý do cũ [cite: 174, 175]
        $user->status_reason = ($request->status === User::STATUS_LOCKED) 
            ? $request->reason 
            : null;
            
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái tài khoản thành công!',
            'user' => $user
        ]);
    }
}