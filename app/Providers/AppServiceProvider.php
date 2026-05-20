<?php

namespace App\Providers;

use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\FollowRepositoryInterface; // <-- Chèn thêm dòng này
use App\Repositories\PostRepository;
use App\Repositories\ReportRepository;
use App\Repositories\UserRepository;
use App\Repositories\FollowRepository; // <-- Chèn thêm dòng này
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Các phân hệ cũ của nhóm giữ nguyên vẹn
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);

        $this->app->bind(ReportRepositoryInterface::class, ReportRepository::class);

        // =========================================================================
        // PHÂN HỆ MỚI: KẾT NỐI CỘNG ĐỒNG (FOLLOW & FEED)
        // =========================================================================
        $this->app->bind(FollowRepositoryInterface::class, FollowRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}