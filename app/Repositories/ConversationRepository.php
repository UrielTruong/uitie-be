<?php

namespace App\Repositories;

use App\Models\Message;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ConversationRepository implements ConversationRepositoryInterface
{
    public function getConversations(int $userId): array
    {
        // Get distinct users the current user has exchanged DMs with, plus last message
        $conversations = DB::select("
            SELECT
                partner_id,
                content,
                created_at
            FROM (
                SELECT
                    receiver_id AS partner_id,
                    content,
                    created_at,
                    ROW_NUMBER() OVER (PARTITION BY receiver_id ORDER BY created_at DESC) AS rn
                FROM messages
                WHERE sender_id = ? AND group_id IS NULL

                UNION ALL

                SELECT
                    sender_id AS partner_id,
                    content,
                    created_at,
                    ROW_NUMBER() OVER (PARTITION BY sender_id ORDER BY created_at DESC) AS rn
                FROM messages
                WHERE receiver_id = ? AND group_id IS NULL
            ) AS sub
            WHERE rn = 1
            ORDER BY created_at DESC
        ", [$userId, $userId]);

        // De-duplicate partners (keep most recent message per partner)
        $seen = [];
        $result = [];
        foreach ($conversations as $row) {
            if (! isset($seen[$row->partner_id])) {
                $seen[$row->partner_id] = true;
                $result[] = $row;
            }
        }

        return $result;
    }

    public function getMessages(int $userId, int $otherUserId, int $perPage = 30): LengthAwarePaginator
    {
        return Message::with(['sender', 'attachments'])
            ->where('group_id', null)
            ->where(function ($q) use ($userId, $otherUserId) {
                $q->where(fn($q2) => $q2->where('sender_id', $userId)->where('receiver_id', $otherUserId))
                  ->orWhere(fn($q2) => $q2->where('sender_id', $otherUserId)->where('receiver_id', $userId));
            })
            ->latest()
            ->paginate($perPage);
    }

    public function createMessage(array $data): Message
    {
        return Message::create($data);
    }
}
