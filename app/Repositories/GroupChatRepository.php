<?php

namespace App\Repositories;

use App\Models\GroupChat;
use App\Models\GroupMember;
use App\Models\Message;
use App\Repositories\Contracts\GroupChatRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class GroupChatRepository implements GroupChatRepositoryInterface
{
    public function getGroupsForUser(int $userId): Collection
    {
        return GroupChat::whereHas('members', fn($q) => $q->where('user_id', $userId)
                ->whereIn('status', [GroupMember::STATUS_ACCEPTED, GroupMember::STATUS_PENDING]))
            ->with([
                'members' => fn($q) => $q->where('user_id', $userId),
                'messages' => fn($q) => $q->latest()->limit(1)->with('sender'),
            ])
            ->latest()
            ->get();
    }

    public function findById(int $id): ?GroupChat
    {
        return GroupChat::find($id);
    }

    public function create(array $data): GroupChat
    {
        return GroupChat::create($data);
    }

    public function delete(GroupChat $group): void
    {
        $group->delete();
    }

    public function getMessages(int $groupId, int $perPage = 30): LengthAwarePaginator
    {
        return Message::with(['sender', 'attachments'])
            ->where('group_id', $groupId)
            ->latest()
            ->paginate($perPage);
    }

    public function createMessage(array $data): Message
    {
        return Message::create($data);
    }

    public function getMembership(int $groupId, int $userId): ?GroupMember
    {
        return GroupMember::where('group_id', $groupId)->where('user_id', $userId)->first();
    }

    public function addMember(int $groupId, int $userId, string $status = 'Pending'): GroupMember
    {
        return GroupMember::create([
            'group_id'  => $groupId,
            'user_id'   => $userId,
            'status'    => $status,
            'joined_at' => $status === GroupMember::STATUS_ACCEPTED ? now() : null,
        ]);
    }

    public function removeMember(int $groupId, int $userId): void
    {
        GroupMember::where('group_id', $groupId)->where('user_id', $userId)->delete();
    }

    public function updateMemberStatus(GroupMember $member, string $status): void
    {
        $member->update([
            'status'    => $status,
            'joined_at' => $status === GroupMember::STATUS_ACCEPTED ? now() : $member->joined_at,
        ]);
    }

    public function getMembers(int $groupId): Collection
    {
        return GroupMember::with('user')
            ->where('group_id', $groupId)
            ->get();
    }
}
