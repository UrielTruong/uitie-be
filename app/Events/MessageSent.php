<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly array $message,
        public readonly int $senderId,
        public readonly int $receiverId,
    ) {}

    public function broadcastOn(): array
    {
        $min = min($this->senderId, $this->receiverId);
        $max = max($this->senderId, $this->receiverId);

        return [new PrivateChannel("dm.{$min}.{$max}")];
    }

    public function broadcastWith(): array
    {
        return $this->message;
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }
}
