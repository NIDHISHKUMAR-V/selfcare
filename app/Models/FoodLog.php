<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'food_item_id',
        'meal_type',
        'food_name',
        'quantity',
        'serving_size',
        'calories',
        'protein',
        'carbohydrates',
        'fat',
        'meal_date',
        'meal_time',
        'notes',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'meal_date' => 'date',
            'quantity' => 'decimal:2',
            'calories' => 'decimal:2',
            'protein' => 'decimal:2',
            'carbohydrates' => 'decimal:2',
            'fat' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class);
    }
}
