<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerusahaanProfile extends Model
{
    protected $fillable = [
        'user_id',
        'industry',
        'location',
        'website',
        'description',
        'vision',
        'mission',
        'founded_year',
        'employee_count',
        'specializations',
        'office_address',
        'logo_path',
        'cover_path',
        'legal_document_path',
        'contact_email',
        'instagram',
        'linkedin',
        'gallery_photos',
    ];

    protected function casts(): array
    {
        return [
            'specializations' => 'array',
            'gallery_photos' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
