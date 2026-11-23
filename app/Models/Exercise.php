<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity_type',
        'duration_minutes',
        'distance_km',
        'calories_burned',
        'exercise_date',
        'exercise_time',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'distance_km' => 'decimal:2',
            'calories_burned' => 'decimal:2',
            'exercise_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
