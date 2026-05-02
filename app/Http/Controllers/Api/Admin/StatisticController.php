<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

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
}
