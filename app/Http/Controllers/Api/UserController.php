<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateNewUserRequest;
use App\Http\Requests\GetListUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\SearchUserRequest;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * Return a paginated list of users.
     *
     * GET /api/users
     *
     * Query params:
     *   - per_page (int, default 15): number of items per page
     */
    public function getList(GetListUserRequest $request): UserCollection
    {
        $perPage = (int) $request->query('per_page', 15);

        $users = $this->users->paginate($perPage);

        return new UserCollection($users);
    }

    /**
     * Store a newly created resource in storage.
     * 
     * POST /api/users
     */
    public function createNew(CreateNewUserRequest $request): UserResource
    {
        $user = $this->users->create($request->validated());

        return new UserResource($user);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $userId = $request->user_id;
        $currentPassword = $request->current_password;
        $newPassword = $request->new_password;

        $user = $this->users->findById($userId);

        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found',
            ], 404);
        }

        if (! Hash::check($currentPassword, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        if (Hash::check($newPassword, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'New password cannot be the same as the current password',
            ], 400);
        }

        try {
            $user->password = Hash::make($newPassword);
            $user->updated_at = now();
            $user->save();

            return response()->json([
                'status'  => true,
                'message' => 'Password changed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to change password',
            ], 500);
        }
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $email = $request->email;

        $user = $this->users->findByEmail($email);

        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found',
            ], 404);
        }

        //reset to default password
        try {
            $user->password = Hash::make('12345678');
            $user->updated_at = now();
            $user->save();
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to reset password',
            ], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Password reset successfully',
        ]);
    }
    // GET /api/users/search
    public function search(SearchUserRequest $request): UserCollection
    {
        $filters = $request->only(['keyword', 'mssv', 'class_name', 'faculty']);
        $perPage = $request->integer('per_page', 15);

        $users = $this->users->search($filters, $perPage);

        return new UserCollection($users);
    }
}
