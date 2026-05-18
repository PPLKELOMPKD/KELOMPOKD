<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_one_id',
        'participant_two_id',
        'created_by_id',
        'last_message_id',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public static function createBetween(User $first, User $second, User $creator): self
    {
        [$participantOneId, $participantTwoId] = collect([$first->id, $second->id])->sort()->values()->all();

        return DB::transaction(function () use ($creator, $participantOneId, $participantTwoId) {
            $conversation = self::query()->firstOrCreate(
                [
                    'participant_one_id' => $participantOneId,
                    'participant_two_id' => $participantTwoId,
                ],
                [
                    'created_by_id' => $creator->id,
                ]
            );

            foreach ([$participantOneId, $participantTwoId] as $userId) {
                $conversation->participants()->firstOrCreate(['user_id' => $userId]);
            }

            return $conversation->fresh(['participants.user']);
        });
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('participant_one_id', $user->id)
                ->orWhere('participant_two_id', $user->id);
        });
    }

    public function participantOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_one_id');
    }

    public function participantTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_two_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(ConversationMessage::class, 'last_message_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function hasParticipant(User $user): bool
    {
        return $this->participant_one_id === $user->id || $this->participant_two_id === $user->id;
    }

    public function otherParticipant(User $user): ?User
    {
        if ($this->relationLoaded('participantOne') && $this->relationLoaded('participantTwo')) {
            return $this->participant_one_id === $user->id ? $this->participantTwo : $this->participantOne;
        }

        $otherId = $this->participant_one_id === $user->id ? $this->participant_two_id : $this->participant_one_id;

        return User::query()->find($otherId);
    }

    public function participantRecord(User $user): ?ConversationParticipant
    {
        if ($this->relationLoaded('participants')) {
            return $this->participants->firstWhere('user_id', $user->id);
        }

        return $this->participants()->where('user_id', $user->id)->first();
    }

    public function unreadCountFor(User $user): int
    {
        $participant = $this->participantRecord($user);
        $lastReadId = $participant?->last_read_message_id ?? 0;

        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('id', '>', $lastReadId)
            ->count();
    }
}
