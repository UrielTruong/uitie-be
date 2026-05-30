<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Attachment;
use App\Models\User;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function __construct(
        private ConversationRepositoryInterface $conversationRepository,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId        = $request->attributes->get('user_id');
        $conversations = $this->conversationRepository->getConversations($userId);

        $data = collect($conversations)->map(function ($row) {
            $user = User::select('id', 'full_name')->find($row->partner_id);
            if (! $user) return null;

            return [
                'user'         => ['id' => $user->id, 'full_name' => $user->full_name],
                'last_message' => [
                    'content'    => $row->content,
                    'created_at' => $row->created_at,
                ],
            ];
        })->filter()->values();

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function messages(Request $request, int $userId): JsonResponse
    {
        $authId   = $request->attributes->get('user_id');
        $paginated = $this->conversationRepository->getMessages($authId, $userId);

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

    public function send(SendMessageRequest $request, int $userId): JsonResponse
    {
        $authId = $request->attributes->get('user_id');

        DB::beginTransaction();
        try {
            $message = $this->conversationRepository->createMessage([
                'sender_id'   => $authId,
                'receiver_id' => $userId,
                'group_id'    => null,
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

            event(new MessageSent($payload, $authId, $userId));

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
}
