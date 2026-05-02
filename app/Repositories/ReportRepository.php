<?php

namespace App\Repositories;

use App\Models\Report;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

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
}
