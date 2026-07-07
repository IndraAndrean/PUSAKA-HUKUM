<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SatisfactionSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'respondent_key',
        'respondent_type',
        'accessibility_rating',
        'speed_rating',
        'content_rating',
        'ease_rating',
        'overall_rating',
        'found_document',
        'search_duration_seconds',
        'most_useful_feature',
        'feedback',
        'ip_hash',
        'user_agent',
    ];

    protected $casts = [
        'found_document' => 'boolean',
    ];

    protected $appends = [
        'satisfaction_percentage',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSatisfactionPercentageAttribute(): int
    {
        $total = $this->accessibility_rating
            + $this->speed_rating
            + $this->content_rating
            + $this->ease_rating
            + $this->overall_rating;

        return (int) round(($total / 25) * 100);
    }
}
