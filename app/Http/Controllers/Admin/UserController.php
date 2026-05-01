<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // =========================================================================
    // 1. QUẢN LÝ SINH VIÊN (DÀNH CHO ADMIN & SUPER ADMIN)
    // =========================================================================

    /**
     * Lấy danh sách Sinh viên
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->where('role', User::ROLE_STUDENT)
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
     * Cập nhật thông tin chi tiết của Sinh viên
     */
    public function updateStudent(Request $request, $id)
    {
        $student = User::where('id', $id)->where('role', User::ROLE_STUDENT)->firstOrFail();

        $request->validate([
            'full_name'     => 'required|string|max:255',
            'mssv'          => 'required|string|unique:users,mssv,' . $id,
            'phone_number'  => 'nullable|string|max:15',
            'faculty'       => 'nullable|string',
            'class_name'    => 'nullable|string',
            'academic_year' => 'nullable|string',
        ]);

        $student->update($request->only([
            'full_name', 'mssv', 'phone_number', 'faculty', 'class_name', 'academic_year'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin sinh viên thành công!',
            'data'    => $student
        ]);
    }

    /**
     * Xóa tài khoản Sinh viên
     */
    public function deleteStudent($id)
    {
        $student = User::where('id', $id)->where('role', User::ROLE_STUDENT)->firstOrFail();
        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa tài khoản sinh viên thành công!'
        ]);
    }

    /**
     * Khóa hoặc Mở khóa tài khoản (Dùng chung cho cả Admin & Student)
     */
    public function updateStatus(Request $request, $id)
    {
        $validReasons = implode(',', array_keys(User::BLOCK_REASONS));

        $request->validate([
            'status' => 'required|in:' . User::STATUS_ACTIVE . ',' . User::STATUS_LOCKED,
            'reason_key' => 'required_if:status,' . User::STATUS_LOCKED . '|in:' . $validReasons,
            'other_detail' => 'required_if:reason_key,OTHER|string|nullable' 
        ], [
            'reason_key.required_if' => 'Vui lòng chọn một lý do để khóa tài khoản.',
            'other_detail.required_if' => 'Vui lòng nhập chi tiết cho lý do khác.'
        ]);

        $user = User::findOrFail($id);
        $user->status = $request->status; 
        
        if ($request->status === User::STATUS_LOCKED) {
            $user->status_reason = ($request->reason_key === 'OTHER') 
                ? $request->other_detail 
                : User::BLOCK_REASONS[$request->reason_key];
        } else {
            $user->status_reason = null;
        }
            
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái tài khoản thành công!',
            'user' => $user
        ]);
    }

    // =========================================================================
    // 2. QUẢN LÝ ADMIN (CHỈ DÀNH RIÊNG CHO SUPER ADMIN)
    // =========================================================================

    /**
     * Lấy danh sách tất cả các tài khoản Admin
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
     * Tạo tài khoản Admin mới
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
            'password'     => $request->password,
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
     * Cập nhật thông tin Admin cấp dưới
     */
    public function updateAdmin(Request $request, $id)
    {
        $admin = User::where('id', $id)->where('role', User::ROLE_ADMIN)->firstOrFail();

        $request->validate([
            'full_name'    => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:15',
            'faculty'      => 'nullable|string',
            'password'     => 'nullable|string|min:6',
        ]);

        $admin->full_name = $request->full_name;
        $admin->phone_number = $request->phone_number;
        $admin->faculty = $request->faculty;

        if ($request->filled('password')) {
            $admin->password = $request->password;
        }

        $admin->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin Admin thành công!',
            'data'    => $admin
        ]);
    }

    /**
     * Xóa tài khoản Admin
     */
    public function deleteAdmin($id)
    {
        $admin = User::where('id', $id)->where('role', User::ROLE_ADMIN)->firstOrFail();
        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa tài khoản Admin thành công!'
        ]);
    }
}