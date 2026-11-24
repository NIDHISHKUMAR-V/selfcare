<?php

namespace App\Models;

use Carbon\Carbon;
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
    public function isCompletedOn(string|Carbon $date): bool
    {
        $dateStr = $date instanceof Carbon ? $date->toDateString() : Carbon::parse($date)->toDateString();

        return $this->habitLogs()
            ->whereDate('completed_date', $dateStr)
            ->where('is_completed', true)
            ->exists();
    }

    /**
     * Calculate current streak in consecutive days.
     */
    public function getCurrentStreakAttribute(): int
    {
        $logs = $this->habitLogs()
            ->where('is_completed', true)
            ->orderByDesc('completed_date')
            ->pluck('completed_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->values();

        if ($logs->isEmpty()) {
            return 0;
        }

        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        // If neither today nor yesterday is completed, the streak is 0
        if ($logs[0] !== $today && $logs[0] !== $yesterday) {
            return 0;
        }

        $streak = 0;
        $current = Carbon::parse($logs[0]);

        foreach ($logs as $logDate) {
            if ($logDate === $current->toDateString()) {
                $streak++;
                $current->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Calculate best (longest) streak ever in consecutive days.
     */
    public function getBestStreakAttribute(): int
    {
        $dates = $this->habitLogs()
            ->where('is_completed', true)
            ->orderBy('completed_date')
            ->pluck('completed_date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $best = 1;
        $currentStreak = 1;

        for ($i = 1; $i < $dates->count(); $i++) {
            $prev = Carbon::parse($dates[$i - 1]);
            $curr = Carbon::parse($dates[$i]);

            if ($curr->diffInDays($prev) === 1) {
                $currentStreak++;
                if ($currentStreak > $best) {
                    $best = $currentStreak;
                }
            } else {
                $currentStreak = 1;
            }
        }

        return $best;
    }
}
