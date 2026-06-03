<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Internship extends Model
{
    /** @use HasFactory<\Database\Factories\InternshipFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'company_name',
        'company_logo',
        'location',
        'education_level',
        'work_type',
        'duration',
        'requirements',
        'benefits',
        'salary',
        'salary_range',
        'deadline_at',
        'quota',
        'is_published',
        // Moderasi
        'moderation_status',
        'rejection_reason',
        'moderated_by',
        'moderated_at',
    ];

    protected $appends = ['is_expired'];

    protected function casts(): array
    {
        return [
            'deadline_at'  => 'datetime',
            'moderated_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    /** Accessor: apakah lowongan sudah melewati deadline */
    public function getIsExpiredAttribute(): bool
    {
        return $this->deadline_at ? $this->deadline_at->isPast() : false;
    }

    /** Scope: lowongan yang masih aktif (belum expired) */
    public function scopeActive($query)
    {
        return $query->where('deadline_at', '>=', now());
    }

    /** Scope: lowongan yang sudah expired */
    public function scopeExpired($query)
    {
        return $query->where('deadline_at', '<', now());
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /** Admin yang melakukan moderasi terakhir */
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    /** Scope: hanya lowongan yang sudah approved/tayang */
    public function scopeApproved($query)
    {
        return $query->where('moderation_status', 'approved');
    }

    /** Scope: hanya lowongan pending */
    public function scopePending($query)
    {
        return $query->where('moderation_status', 'pending');
    }
}
