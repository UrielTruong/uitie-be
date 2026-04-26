<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra xem người dùng đã đăng nhập chưa và có phải Admin/Super Admin không [cite: 348, 537]
        if ($request->user() && ($request->user()->role === User::ROLE_ADMIN || $request->user()->role === User::ROLE_SUPER_ADMIN)) {
            return $next($request);
        }

        // Nếu không phải Admin, trả về lỗi 403 (Cấm truy cập) [cite: 117, 561]
        return response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện hành động này.'
        ], 403);
    }
}