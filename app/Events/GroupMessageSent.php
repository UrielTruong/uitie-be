<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly array $message,
        public readonly int $groupId,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("group.{$this->groupId}")];
    }

    public function broadcastWith(): array
    {
        return $this->message;
    }

    public function broadcastAs(): string
    {
        return 'GroupMessageSent';
    }
}
