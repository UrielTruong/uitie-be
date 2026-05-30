<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface ConversationRepositoryInterface
{
    public function getConversations(int $userId): array;

    public function getMessages(int $userId, int $otherUserId, int $perPage = 30): LengthAwarePaginator;

    public function createMessage(array $data): \App\Models\Message;
}
