<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSearchUserRequest;
use App\Http\Requests\Admin\ExportUserPdfRequest;
use App\Http\Resources\Admin\AdminUserResource;
use App\Http\Resources\UserCollection;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;

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
