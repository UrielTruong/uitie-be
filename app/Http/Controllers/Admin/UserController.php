<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * 1. Lấy danh sách Sinh viên (Dành cho Admin/Super Admin)
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->where('role', User::ROLE_STUDENT) // Chỉ lấy sinh viên
            ->when($request->faculty, function ($query, $faculty) {
                return $query->where('faculty', $faculty);
            })
            ->when($request->class_name, function ($query, $className) {
                return $query->where('class_name', $className);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * 2. Khóa hoặc Mở khóa tài khoản (Dành cho Admin/Super Admin)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:' . User::STATUS_ACTIVE . ',' . User::STATUS_LOCKED,
            'reason' => 'required_if:status,' . User::STATUS_LOCKED . '|string|nullable' 
        ], [
            'reason.required_if' => 'Vui lòng nhập lý do khi khóa tài khoản.'
        ]);

        $user = User::findOrFail($id);
        
        // Cập nhật trạng thái và lý do dùng Constant
        $user->status = $request->status; 
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

    // =========================================================================
    // DÀNH RIÊNG CHO SUPER ADMIN (Quản lý Admin cấp dưới)
    // =========================================================================

    /**
     * 3. Lấy danh sách tất cả các tài khoản Admin
     */
    public function getAdminList()
    {
        $admins = User::where('role', User::ROLE_ADMIN)
            ->orderBy('full_name', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $admins
        ]);
    }

    /**
     * 4. Tạo tài khoản Admin mới
     */
    public function createAdmin(Request $request)
    {
        $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:6',
            'mssv'         => 'required|string|unique:users,mssv',
            'phone_number' => 'nullable|string|max:15',
            'faculty'      => 'nullable|string',
        ]);

        $admin = User::create([
            'full_name'    => $request->full_name,
            'email'        => $request->email,
            'password'     => $request->password, // Tự động hash do $casts trong Model
            'mssv'         => $request->mssv,
            'phone_number' => $request->phone_number,
            'faculty'      => $request->faculty,
            'role'         => User::ROLE_ADMIN,
            'status'       => User::STATUS_ACTIVE,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo tài khoản Admin thành công!',
            'data'    => $admin
        ], 201);
    }
    /**
     * 5. Cập nhật thông tin Admin khác
     */
    public function updateAdmin(Request $request, $id)
    {
        $admin = User::where('id', $id)->where('role', User::ROLE_ADMIN)->firstOrFail();

        $request->validate([
            'full_name'    => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:15',
            'faculty'      => 'nullable|string',
            'password'     => 'nullable|string|min:6', // Chỉ nhập khi muốn đổi pass
        ]);

        $admin->full_name = $request->full_name;
        $admin->phone_number = $request->phone_number;
        $admin->faculty = $request->faculty;

        if ($request->filled('password')) {
            $admin->password = $request->password; // Tự động hash do Model Cast
        }

        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin Admin thành công!',
            'data'    => $admin
        ]);
    }

    /**
     * 6. Xóa tài khoản Admin
     */
    public function deleteAdmin($id)
    {
        // Chỉ cho phép xóa nếu đúng là role Admin
        $admin = User::where('id', $id)->where('role', User::ROLE_ADMIN)->firstOrFail();
        
        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa tài khoản Admin thành công!'
        ]);
    }
}