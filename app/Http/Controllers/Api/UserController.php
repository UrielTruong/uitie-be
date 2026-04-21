<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateNewUserRequest;
use App\Http\Requests\GetListUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;

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
}
