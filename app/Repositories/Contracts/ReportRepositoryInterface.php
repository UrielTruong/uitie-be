<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ReportRepositoryInterface
{
    /**
     * Lấy toàn bộ report theo bộ filter để xuất PDF (không phân trang).
     *
     * @param array<string, mixed> $filters
     */
    public function getAllForExport(array $filters = []): Collection;
}
