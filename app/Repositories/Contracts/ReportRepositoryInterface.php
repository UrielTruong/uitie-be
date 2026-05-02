<?php

namespace App\Repositories\Contracts;

use App\Models\Report;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReportRepositoryInterface
{
    /**
     * Lấy toàn bộ report theo bộ filter để xuất PDF (không phân trang).
     *
     * @param array<string, mixed> $filters
     */
    public function getAllForExport(array $filters = []): Collection;

    public function adminSearch(array $filters, int $perPage): LengthAwarePaginator;

    public function findById(int $id): ?Report;

    public function validate(Report $report, string $adminId, string $status): ?Report;
}
