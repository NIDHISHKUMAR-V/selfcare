<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FoodLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'meal_type' => ['required', 'string', 'in:breakfast,lunch,dinner,snack'],
            'food_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:1000'],
            'serving_size' => ['nullable', 'string', 'max:100'],
            'calories' => ['required', 'numeric', 'min:0', 'max:10000'],
            'protein' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'carbohydrates' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'fat' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'meal_date' => ['required', 'date'],
            'meal_time' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
        ];
    }
}
