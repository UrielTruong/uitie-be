<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSearchUserRequest;
use App\Http\Requests\Admin\ExportUserPdfRequest;
use App\Http\Requests\Admin\CreateNewUserRequest;
use App\Http\Requests\Admin\DeleteUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Resources\Admin\AdminUserResource;
use App\Http\Resources\UserCollection;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

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

    public function createNewUser(CreateNewUserRequest $request)
    {
        $user = $this->users->create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            //auto set password to 12345678
            'password' => Hash::make('12345678'),
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    public function updateUser(UpdateUserRequest $request, $id)
    {
        $user = $this->users->findById($id);

        $updated = $this->users->update($id, [
            'full_name' => $request->full_name,
            'role' => $request->role,
            'status' => $request->status,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => new AdminUserResource($updated)
        ], 200);
    }
    public function deleteUser(int $id, \Illuminate\Http\Request $request): JsonResponse
    {
        $user = $this->users->findById($id);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not found',
            ], 404);
        }

        $this->users->delete($id);

        return response()->json([
            'status'  => true,
            'message' => 'User deleted successfully',
        ]);
    }

    public function exportPdf(ExportUserPdfRequest $request): Response
    {
        $filters = $request->only(['keyword', 'mssv', 'class_name', 'faculty', 'status', 'role']);

        $users = $this->users->getAllForExport($filters);

        // Thống kê cho Report Footer
        $stats = [
            'total'    => $users->count(),
            'active'   => $users->where('status', User::STATUS_ACTIVE)->count(),
            'inactive' => $users->where('status', User::STATUS_INACTIVE)->count(),
            'locked'   => $users->where('status', User::STATUS_LOCKED)->count(),
            'student'  => $users->where('role', User::ROLE_STUDENT)->count(),
            'admin'    => $users->where('role', User::ROLE_ADMIN)->count(),
        ];

        $pdf = Pdf::loadView('reports.users-pdf', [
            'users'       => $users,
            'stats'       => $stats,
            'filters'     => $filters,
            'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
            'exportedBy'  => $request->attributes->get('user_id'), // từ JWT middleware
        ])
            ->setPaper('a4', 'landscape') // landscape vì bảng có nhiều cột
            ->setOptions([
                'defaultFont' => 'DejaVu Sans', // hỗ trợ tiếng Việt
                'isHtml5ParserEnabled' => true,
            ]);


        $filename = 'danh-sach-nguoi-dung-' . Carbon::now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}
