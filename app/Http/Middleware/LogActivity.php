<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next)
{
    $response = $next($request);

    // Danh sách các hành động cần ghi log
    $trackedActions = [
        'POST' => ['login', 'register', 'posts'], // Đăng nhập, đăng ký, tạo bài
        'DELETE' => ['posts', 'comments', 'users'] // Xóa bài, xóa cmt, xóa user
    ];

    $method = $request->method();
    $path = $request->path();

    // Kiểm tra xem hành động hiện tại có nằm trong danh sách theo dõi không
    foreach ($trackedActions as $m => $paths) {
        if ($method === $m && \Str::contains($path, $paths)) {
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action_type' => $method . ' ' . $path,
                'payload' => json_encode($request->except(['password', 'password_confirmation'])),
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
            ]);
        }
    }

    return $response;
}
