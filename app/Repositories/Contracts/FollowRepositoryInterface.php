<?php

namespace App\Repositories\Contracts;

interface FollowRepositoryInterface
{
    public function toggleFollow(int $currentUserId, int $targetUserId): array;
    public function getFollowersFeed(int $currentUserId, string $timeFilter);
}