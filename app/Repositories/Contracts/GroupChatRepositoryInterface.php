<?php

namespace App\Repositories\Contracts;

use App\Models\GroupChat;
use App\Models\GroupMember;
use App\Models\Message;
use Illuminate\Pagination\LengthAwarePaginator;

interface GroupChatRepositoryInterface
{
    public function getGroupsForUser(int $userId): \Illuminate\Database\Eloquent\Collection;

    public function findById(int $id): ?GroupChat;

    public function create(array $data): GroupChat;

    public function delete(GroupChat $group): void;

    public function getMessages(int $groupId, int $perPage = 30): LengthAwarePaginator;

    public function createMessage(array $data): Message;

    public function getMembership(int $groupId, int $userId): ?GroupMember;

    public function addMember(int $groupId, int $userId, string $status = 'Pending'): GroupMember;

    public function removeMember(int $groupId, int $userId): void;

    public function updateMemberStatus(GroupMember $member, string $status): void;

    public function getMembers(int $groupId): \Illuminate\Database\Eloquent\Collection;
}
