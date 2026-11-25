<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'activity_type' => ['required', 'string', 'max:50'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
            'distance_km' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'calories_burned' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'exercise_date' => ['required', 'date'],
            'exercise_time' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
