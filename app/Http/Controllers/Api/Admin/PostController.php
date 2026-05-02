<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSearchPostRequest;
use App\Http\Requests\Admin\ExportPostPdfRequest;
use App\Http\Resources\Admin\AdminPostResource;
use App\Http\Resources\PostCollection;
use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;

class PostController extends Controller
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
    ) {}

    // GET /api/admin/posts/search
    public function searchPost(AdminSearchPostRequest $request)
    {
        $filters = $request->only(['keyword', 'category_id', 'status', 'visibility', 'user_id', 'from_date', 'to_date']);
        $perPage = $request->integer('per_page', 15);

        $posts = $this->postRepository->adminSearch($filters, $perPage);

        return AdminPostResource::collection($posts);
    }
    public function exportPdf(ExportPostPdfRequest $request): Response
    {
        $filters = $request->only(['keyword', 'category_id', 'status', 'visibility']);

        $posts = $this->postRepository->getAllForExport($filters);

        $stats = [
            'total'    => $posts->count(),
            'accepted' => $posts->where('status', Post::STATUS_ACCEPTED)->count(),
            'pending'  => $posts->where('status', Post::STATUS_PENDING)->count(),
            'rejected' => $posts->where('status', Post::STATUS_REJECTED)->count(),
            'public'   => $posts->where('visibility', Post::VISIBILITY_PUBLIC)->count(),
            'private'  => $posts->where('visibility', Post::VISIBILITY_PRIVATE)->count(),
        ];

        $pdf = Pdf::loadView('reports.posts-pdf', [
            'posts'       => $posts,
            'stats'       => $stats,
            'filters'     => $filters,
            'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
        ])
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = 'danh-sach-bai-viet-' . Carbon::now()->format('Ymd-His') . '.pdf';

        return $pdf->download($filename);
    }
}
