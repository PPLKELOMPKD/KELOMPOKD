<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    public const MODERATION_PENDING  = 'pending';
    public const MODERATION_APPROVED = 'approved';
    public const MODERATION_REJECTED = 'rejected';

    protected $fillable = [
        'company_id',
        'title',
        'category',
        'description',
        'date',
        'start_time',
        'end_time',
        'location',
        'type',
        'status',
        'max_participants',
        'moderation_status',
        'rejection_reason',
        'moderated_by',
        'moderated_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(EventRating::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'published' && $this->date->isFuture();
    }

    /**
     * Apakah event sudah selesai?
     * Selesai = status 'completed' ATAU tanggal sudah terlewati.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' || $this->date->isPast();
    }
}
