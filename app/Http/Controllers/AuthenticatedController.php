<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\LoginResource;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthenticatedController extends Controller
{
    public function __construct(private JwtService $jwtService) {}

    public function login(LoginRequest $request): LoginResource|JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid credentials',
                    'data'    => null,
                ], 400);
            }

            // --- KIỂM TRA TÀI KHOẢN BỊ KHÓA ---
            if ($user->status === User::STATUS_LOCKED) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Tài khoản của bạn đã bị khóa. Lý do: ' . ($user->status_reason ?? 'Vi phạm quy định hệ thống.'),
                    'data'    => null,
                ], 403);
            }
            // ---------------------------------------

            $token = $this->jwtService->encode($user);

            return new LoginResource($user, $token);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid credentials',
                'data'    => null,
            ], 400);
        }
    }
}