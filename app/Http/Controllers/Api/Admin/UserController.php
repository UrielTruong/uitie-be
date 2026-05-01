<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSearchUserRequest;
use App\Http\Resources\Admin\AdminUserResource;
use App\Http\Resources\UserCollection;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function searchUser(AdminSearchUserRequest $request)
    {
        $filters = $request->only(['keyword', 'mssv', 'class_name', 'faculty', 'role', 'status']);
        $perPage = $request->integer('per_page', 15);

        $users = $this->users->adminSearch($filters, $perPage);

        return AdminUserResource::collection($users);
    }
}
