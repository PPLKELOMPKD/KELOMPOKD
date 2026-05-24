<?php

namespace App\Http\Controllers;

use App\Events\DirectMessageSent;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DirectMessageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->forUser($user)
            ->with(['participantOne', 'participantTwo', 'lastMessage.sender', 'participants'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'conversations' => $conversations->map(fn (Conversation $conversation) => $this->conversationPayload($conversation, $user))->values(),
            'unread_count' => $this->totalUnreadCount($user),
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $user = $request->user();
        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->whereKeyNot($user->id)
            ->where('status', User::STATUS_ACTIVE)
            ->when(! $user->isAdmin(), function ($query) use ($user) {
                $query->where('role', $user->isMahasiswa() ? User::ROLE_PERUSAHAAN : User::ROLE_MAHASISWA);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'email', 'role']);

        return response()->json(['users' => $users]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = $request->user();
        $recipient = User::query()->findOrFail($data['recipient_id']);

        if ($recipient->is($user)) {
            return response()->json(['message' => 'Anda tidak dapat mengirim pesan ke diri sendiri.'], 422);
        }

        abort_unless($this->canStartConversation($user, $recipient), 403);

        $existed = Conversation::query()
            ->where(function ($query) use ($user, $recipient) {
                $query->where('participant_one_id', min($user->id, $recipient->id))
                    ->where('participant_two_id', max($user->id, $recipient->id));
            })
            ->exists();

        $conversation = Conversation::createBetween($user, $recipient, $user)
            ->load(['participantOne', 'participantTwo', 'lastMessage.sender', 'participants']);

        return response()->json([
            'conversation' => $this->conversationPayload($conversation, $user),
            'unread_count' => $this->totalUnreadCount($user),
        ], $existed ? 200 : 201);
    }

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        abort_unless($conversation->hasParticipant($user), 403);

        $messages = $conversation->messages()
            ->with('sender:id,name,role')
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn (ConversationMessage $message) => $this->messagePayload($message));

        return response()->json([
            'messages' => $messages,
            'conversation' => $this->conversationPayload(
                $conversation->load(['participantOne', 'participantTwo', 'lastMessage.sender', 'participants']),
                $user
            ),
        ]);
    }

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        abort_unless($conversation->hasParticipant($user), 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = DB::transaction(function () use ($conversation, $data, $user) {
            $message = $conversation->messages()->create([
                'sender_id' => $user->id,
                'body' => trim($data['body']),
            ]);

            $conversation->update([
                'last_message_id' => $message->id,
                'last_message_at' => $message->created_at,
            ]);

            $conversation->participants()
                ->where('user_id', $user->id)
                ->update([
                    'last_read_message_id' => $message->id,
                    'last_read_at' => now(),
                ]);

            return $message->load('sender:id,name,role');
        });

        $recipient = $conversation->otherParticipant($user);

        if ($recipient) {
            DirectMessageSent::dispatch($message, $recipient);
        }

        return response()->json([
            'message' => $this->messagePayload($message),
            'conversation' => $this->conversationPayload(
                $conversation->fresh(['participantOne', 'participantTwo', 'lastMessage.sender', 'participants']),
                $user
            ),
        ], 201);
    }

    public function read(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        abort_unless($conversation->hasParticipant($user), 403);

        $lastMessage = $conversation->messages()->latest('id')->first();

        $conversation->participants()
            ->where('user_id', $user->id)
            ->update([
                'last_read_message_id' => $lastMessage?->id,
                'last_read_at' => now(),
            ]);

        $conversation = $conversation->fresh(['participantOne', 'participantTwo', 'lastMessage.sender', 'participants']);

        return response()->json([
            'conversation' => $this->conversationPayload($conversation, $user),
            'unread_count' => $this->totalUnreadCount($user),
        ]);
    }

    private function canStartConversation(User $user, User $recipient): bool
    {
        if ($recipient->status !== User::STATUS_ACTIVE || $recipient->trashed()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isMahasiswa()) {
            return $recipient->isPerusahaan();
        }

        if ($user->isPerusahaan()) {
            return $recipient->isMahasiswa();
        }

        return false;
    }

    private function totalUnreadCount(User $user): int
    {
        return Conversation::query()
            ->forUser($user)
            ->with('participants')
            ->get()
            ->sum(fn (Conversation $conversation) => $conversation->unreadCountFor($user));
    }

    private function conversationPayload(Conversation $conversation, User $viewer): array
    {
        $other = $conversation->otherParticipant($viewer);
        $lastMessage = $conversation->lastMessage;

        return [
            'id' => $conversation->id,
            'recipient' => $other ? [
                'id' => $other->id,
                'name' => $other->name,
                'email' => $other->email,
                'role' => $other->role,
            ] : null,
            'last_message' => $lastMessage ? $this->messagePayload($lastMessage) : null,
            'last_message_at' => $conversation->last_message_at?->toISOString(),
            'last_message_at_human' => $conversation->last_message_at?->format('H:i'),
            'unread_count' => $conversation->unreadCountFor($viewer),
        ];
    }

    private function messagePayload(ConversationMessage $message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'body' => $message->body,
            'created_at' => $message->created_at?->toISOString(),
            'created_at_human' => $message->created_at?->format('H:i'),
            'sender' => $message->relationLoaded('sender') && $message->sender ? [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'role' => $message->sender->role,
            ] : null,
        ];
    }
}
