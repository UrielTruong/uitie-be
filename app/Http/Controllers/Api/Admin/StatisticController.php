<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;

class StatisticController extends Controller
{
    public function __construct(
        private  UserRepositoryInterface $users,
        private ReportRepositoryInterface $reports,
        private PostRepositoryInterface $posts,
    ) {}
    public function getStatistic()
    {
        $users = $this->users->countUsers();
        $reports = $this->reports->countReports();
        $posts = $this->posts->countPosts();
        $postByCategory = $this->posts->countPostsByCategory();

        return response()->json([
            'users' => $users,
            'reports' => $reports,
            'posts' => $posts,
            'postByCategory' => $postByCategory,
        ]);
    }

    public function exportPdf(): Response
    {
        $totalUsers    = $this->users->countUsers();
        $totalReports  = $this->reports->countReports();
        $totalPosts    = $this->posts->countPosts();
        $postByCategory = $this->posts->countPostsByCategory();

        $stats = [
            'users'   => $totalUsers,
            'reports' => $totalReports,
            'posts'   => $totalPosts,
        ];

        $pdf = Pdf::loadView('reports.statistic-pdf', [
            'stats'          => $stats,
            'postByCategory' => $postByCategory,
            'generatedAt'    => Carbon::now()->format('d/m/Y H:i:s'),
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = 'thong-ke-tong-quan-' . Carbon::now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}
