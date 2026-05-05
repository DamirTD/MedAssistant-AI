<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosisHistory extends Model
{
    protected $fillable = [
        'user_id',
        'description',
        'age',
        'gender',
        'has_image',
        'request_payload',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'has_image' => 'boolean',
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
