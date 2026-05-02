<?php

namespace App\Repositories;

use App\Models\Report;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportRepository implements ReportRepositoryInterface
{
    public function getAllForExport(array $filters = []): Collection
    {
        $query = Report::with([
            'reporter',        // người gửi báo cáo
            'reportedUser',    // user bị báo cáo (nếu target_type = User)
            'reportedPost',    // post bị báo cáo (nếu target_type = Post)
            'resolver',        // admin xử lý
        ]);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['target_type'])) {
            $query->where('target_type', $filters['target_type']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function adminSearch(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Report::with(['reporter', 'reportedUser', 'reportedPost', 'resolver']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['target_type'])) {
            $query->where('target_type', $filters['target_type']);
        }

        if (!empty($filters['keyword'])) {
            $query->whereHas('reporter', function ($q) use ($filters) {
                $q->where('full_name', 'like', "%{$filters['keyword']}%")
                    ->orWhere('email', 'like', "%{$filters['keyword']}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function findById(int $id): ?Report
    {
        return Report::with(['reporter', 'reportedUser', 'reportedPost', 'resolver'])->find($id);
    }

    public function validate(Report $report, string $adminId, string $status): ?Report
    {
        $report->update([
            'status'      => $status,
            'resolved_by' => $adminId,
            'resolved_at' => Carbon::now(),
        ]);

        return $report->fresh(['reporter', 'reportedUser', 'reportedPost', 'resolver']);
    }

    public function countReports()
    {
        return Report::where('status', Report::STATUS_PENDING)->count();
    }
}
