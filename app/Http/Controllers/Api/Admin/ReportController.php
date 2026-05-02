<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSearchReportRequest;
use App\Http\Requests\Admin\ExportReportPdfRequest;
use App\Http\Requests\Admin\ValidateReportRequest;
use App\Http\Resources\Admin\AdminReportResource;
use App\Models\Report;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    public function __construct(
        private ReportRepositoryInterface $reportRepository
    ) {}
    //list reports
    public function searchReport(AdminSearchReportRequest $request)
    {
        $filters = $request->only(['status', 'target_type', 'keyword']);
        $perPage = $request->integer('per_page', 15);

        //order by id desc
        $reports = $this->reportRepository->adminSearch($filters, $perPage);

        return AdminReportResource::collection($reports);
    }
    // validate report
    public function validateReport(ValidateReportRequest $request, int $id): JsonResponse
    {
        $report = $this->reportRepository->findById($id);

        if (!$report) {
            return response()->json(['message' => 'Report not found.'], 404);
        }

        if ($report->status !== Report::STATUS_PENDING) {
            return response()->json(['message' => 'Only pending reports can be validated.'], 422);
        }

        $adminId = (string) $request->attributes->get('user_id');

        $validated = $this->reportRepository->validate($report, $adminId, $request->status);

        return response()->json([
            'message' => 'Report validated successfully.',
            'status' => $validated->status,
            'resolved_by_admin_id' => $validated->resolved_by
        ]);
    }

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
