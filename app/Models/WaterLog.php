<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount_ml',
        'log_date',
        'log_time',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_ml' => 'integer',
            'log_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
