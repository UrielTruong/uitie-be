<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportReportPdfRequest;
use App\Models\Report;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function __construct(
        private ReportRepositoryInterface $reportRepository
    ) {}

    /**
     * Xuất danh sách report ra file PDF.
     *
     * GET /api/report/export-pdf
     */
    public function exportPdf(ExportReportPdfRequest $request): Response
    {
        $filters = $request->only(['status', 'target_type']);

        $reports = $this->reportRepository->getAllForExport($filters);

        $stats = [
            'total'     => $reports->count(),
            'pending'   => $reports->where('status', Report::STATUS_PENDING)->count(),
            'resolved'  => $reports->where('status', Report::STATUS_RESOLVED)->count(),
            'dismissed' => $reports->where('status', Report::STATUS_DISMISSED)->count(),
            'userType'  => $reports->where('target_type', Report::TARGET_USER)->count(),
            'postType'  => $reports->where('target_type', Report::TARGET_POST)->count(),
        ];

        $pdf = Pdf::loadView('reports.reports-pdf', [
            'reports'     => $reports,
            'stats'       => $stats,
            'filters'     => $filters,
            'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
        ])
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = 'danh-sach-bao-cao-' . Carbon::now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}
