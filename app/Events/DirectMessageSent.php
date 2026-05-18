<?php

namespace App\Events;

use App\Models\ConversationMessage;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DirectMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ConversationMessage $message,
        public User $recipient,
    ) {
        $this->message->loadMissing('sender', 'conversation.participantOne', 'conversation.participantTwo');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.'.$this->recipient->id),
            new PrivateChannel('conversations.'.$this->message->conversation_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'direct-message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->message->conversation_id,
                'sender_id' => $this->message->sender_id,
                'body' => $this->message->body,
                'created_at' => $this->message->created_at?->toISOString(),
                'created_at_human' => $this->message->created_at?->format('H:i'),
            ],
            'conversation_id' => $this->message->conversation_id,
            'recipient_id' => $this->recipient->id,
        ];
    }
}
