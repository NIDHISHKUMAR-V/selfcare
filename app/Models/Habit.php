<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'category',
        'frequency',
        'target_days_per_week',
        'color',
        'icon',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'target_days_per_week' => 'integer',
            'is_archived' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function habitLogs(): HasMany
    {
        return $this->hasMany(HabitLog::class);
    }

    /**
     * Check if this habit is completed for a given date.
     */
    public function isCompletedOn(string $date): bool
    {
        return $this->habitLogs()->where('completed_date', $date)->where('is_completed', true)->exists();
    }
}
