<?php

namespace App\Http\Controllers;

use App\Events\GroupMessageSent;
use App\Http\Requests\CreateGroupRequest;
use App\Http\Requests\InviteMemberRequest;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\GroupChatResource;
use App\Http\Resources\GroupMemberResource;
use App\Http\Resources\MessageResource;
use App\Models\Attachment;
use App\Models\GroupMember;
use App\Repositories\Contracts\GroupChatRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GroupChatController extends Controller
{
    public function __construct(
        private GroupChatRepositoryInterface $groupChatRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId = $request->attributes->get('user_id');
        $groups = $this->groupChatRepository->getGroupsForUser($userId);

        return response()->json([
            'status' => true,
            'data'   => GroupChatResource::collection($groups),
        ]);
    }

    public function store(CreateGroupRequest $request): JsonResponse
    {
        $authId = $request->attributes->get('user_id');

        DB::beginTransaction();
        try {
            $group = $this->groupChatRepository->create([
                'group_name' => $request->group_name,
                'created_by' => $authId,
            ]);

            // Creator is automatically an accepted member
            $this->groupChatRepository->addMember($group->id, $authId, GroupMember::STATUS_ACCEPTED);

            // Invite additional members (optional)
            foreach ((array) $request->input('member_ids', []) as $memberId) {
                if ((int) $memberId !== $authId) {
                    $this->groupChatRepository->addMember($group->id, (int) $memberId, GroupMember::STATUS_PENDING);
                }
            }

            DB::commit();

            $group->load(['members' => fn($q) => $q->where('user_id', $authId), 'messages']);

            return response()->json([
                'status'  => true,
                'message' => 'Group created.',
                'data'    => new GroupChatResource($group),
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $authId = $request->attributes->get('user_id');
        $group  = $this->groupChatRepository->findById($id);

        if (! $group) {
            return response()->json(['status' => false, 'message' => 'Group not found.'], 404);
        }

        $membership = $this->groupChatRepository->getMembership($id, $authId);
        if (! $membership) {
            return response()->json(['status' => false, 'message' => 'Not a member of this group.'], 403);
        }

        $members = $this->groupChatRepository->getMembers($id);

        return response()->json([
            'status' => true,
            'data'   => [
                'id'         => $group->id,
                'group_name' => $group->group_name,
                'created_by' => $group->created_by,
                'my_status'  => $membership->status,
                'members'    => GroupMemberResource::collection($members),
            ],
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $authId = $request->attributes->get('user_id');
        $group  = $this->groupChatRepository->findById($id);

        if (! $group) {
            return response()->json(['status' => false, 'message' => 'Group not found.'], 404);
        }

        if ((int) $group->created_by !== $authId) {
            return response()->json(['status' => false, 'message' => 'Only the group owner can delete this group.'], 403);
        }

        $this->groupChatRepository->delete($group);

        return response()->json(['status' => true, 'message' => 'Group deleted.']);
    }

    public function messages(Request $request, int $id): JsonResponse
    {
        $authId     = $request->attributes->get('user_id');
        $membership = $this->groupChatRepository->getMembership($id, $authId);

        if (! $membership || $membership->status !== GroupMember::STATUS_ACCEPTED) {
            return response()->json(['status' => false, 'message' => 'Join the group to view messages.'], 403);
        }

        $paginated = $this->groupChatRepository->getMessages($id);

        return response()->json([
            'status' => true,
            'data'   => MessageResource::collection($paginated->getCollection()->reverse()->values()),
            'meta'   => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ],
        ]);
    }

    public function sendMessage(SendMessageRequest $request, int $id): JsonResponse
    {
        $authId     = $request->attributes->get('user_id');
        $membership = $this->groupChatRepository->getMembership($id, $authId);

        if (! $membership || $membership->status !== GroupMember::STATUS_ACCEPTED) {
            return response()->json(['status' => false, 'message' => 'Join the group to send messages.'], 403);
        }

        DB::beginTransaction();
        try {
            $message = $this->groupChatRepository->createMessage([
                'sender_id'   => $authId,
                'receiver_id' => null,
                'group_id'    => $id,
                'content'     => $request->input('content'),
            ]);

            if ($request->filled('attachments')) {
                $attachmentIds = collect($request->attachments)->map(function ($item) {
                    return Attachment::create([
                        'file_url'  => $item['file_url'],
                        'file_type' => $item['file_type'],
                        'file_name' => Cache::pull("fname:{$item['file_url']}"),
                    ])->id;
                })->all();

                $message->attachments()->attach($attachmentIds);
            }

            DB::commit();

            $message->load('sender', 'attachments');
            $payload = (new MessageResource($message))->toArray($request);

            event(new GroupMessageSent($payload, $id));

            return response()->json([
                'status'  => true,
                'message' => 'Message sent.',
                'data'    => $payload,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function invite(InviteMemberRequest $request, int $id): JsonResponse
    {
        $authId    = $request->attributes->get('user_id');
        $group     = $this->groupChatRepository->findById($id);

        if (! $group) {
            return response()->json(['status' => false, 'message' => 'Group not found.'], 404);
        }

        if ((int) $group->created_by !== $authId) {
            return response()->json(['status' => false, 'message' => 'Only the group owner can invite members.'], 403);
        }

        $targetUserId = (int) $request->input('user_id');
        $existing     = $this->groupChatRepository->getMembership($id, $targetUserId);

        if ($existing) {
            return response()->json(['status' => false, 'message' => 'User is already a member of this group.'], 422);
        }

        $member = $this->groupChatRepository->addMember($id, $targetUserId, GroupMember::STATUS_PENDING);
        $member->load('user');

        return response()->json([
            'status'  => true,
            'message' => 'Member invited.',
            'data'    => new GroupMemberResource($member),
        ], 201);
    }

    public function removeMember(Request $request, int $id, int $userId): JsonResponse
    {
        $authId = $request->attributes->get('user_id');
        $group  = $this->groupChatRepository->findById($id);

        if (! $group) {
            return response()->json(['status' => false, 'message' => 'Group not found.'], 404);
        }

        if ((int) $group->created_by !== $authId) {
            return response()->json(['status' => false, 'message' => 'Only the group owner can remove members.'], 403);
        }

        if ($userId === $authId) {
            return response()->json(['status' => false, 'message' => 'Owner cannot remove themselves. Delete the group instead.'], 422);
        }

        $this->groupChatRepository->removeMember($id, $userId);

        return response()->json(['status' => true, 'message' => 'Member removed.']);
    }

    public function join(Request $request, int $id): JsonResponse
    {
        $authId     = $request->attributes->get('user_id');
        $membership = $this->groupChatRepository->getMembership($id, $authId);

        if (! $membership) {
            return response()->json(['status' => false, 'message' => 'You have not been invited to this group.'], 403);
        }

        if ($membership->status === GroupMember::STATUS_ACCEPTED) {
            return response()->json(['status' => false, 'message' => 'Already a member.'], 422);
        }

        $this->groupChatRepository->updateMemberStatus($membership, GroupMember::STATUS_ACCEPTED);

        return response()->json(['status' => true, 'message' => 'Joined the group.']);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $authId     = $request->attributes->get('user_id');
        $membership = $this->groupChatRepository->getMembership($id, $authId);

        if (! $membership) {
            return response()->json(['status' => false, 'message' => 'You have not been invited to this group.'], 403);
        }

        if ($membership->status !== GroupMember::STATUS_PENDING) {
            return response()->json(['status' => false, 'message' => 'No pending invitation to reject.'], 422);
        }

        $this->groupChatRepository->updateMemberStatus($membership, GroupMember::STATUS_REJECTED);

        return response()->json(['status' => true, 'message' => 'Invitation rejected.']);
    }

    public function leave(Request $request, int $id): JsonResponse
    {
        $authId = $request->attributes->get('user_id');
        $group  = $this->groupChatRepository->findById($id);

        if (! $group) {
            return response()->json(['status' => false, 'message' => 'Group not found.'], 404);
        }

        if ((int) $group->created_by === $authId) {
            return response()->json(['status' => false, 'message' => 'Group owner cannot leave. Delete the group instead.'], 422);
        }

        $membership = $this->groupChatRepository->getMembership($id, $authId);
        if (! $membership) {
            return response()->json(['status' => false, 'message' => 'You are not a member of this group.'], 404);
        }

        $this->groupChatRepository->removeMember($id, $authId);

        return response()->json(['status' => true, 'message' => 'Left the group.']);
    }
}
