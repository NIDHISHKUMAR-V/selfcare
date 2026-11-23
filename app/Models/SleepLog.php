<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SleepLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sleep_date',
        'sleep_time',
        'wake_time',
        'duration_minutes',
        'quality_rating',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sleep_date' => 'date',
            'sleep_time' => 'datetime',
            'wake_time' => 'datetime',
            'duration_minutes' => 'integer',
            'quality_rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted duration as "Xh Ym".
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        return "{$hours}h {$minutes}m";
    }

    /**
     * Get quality label.
     */
    public function getQualityLabelAttribute(): string
    {
        return match ($this->quality_rating) {
            1 => 'Poor',
            2 => 'Fair',
            3 => 'Good',
            4 => 'Very Good',
            5 => 'Excellent',
            default => 'Unknown',
        };
    }
}
