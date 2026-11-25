<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PersonalGoalRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1500'],
            'category' => ['required', 'string', 'in:Health,Finance,Fitness,Career,Education,Personal Development'],
            'goal_type' => ['required', 'string', 'in:short_term,long_term'],
            'target_date' => ['nullable', 'date'],
            'progress_percentage' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status' => ['nullable', 'string', 'in:in_progress,completed,on_hold'],
        ];
    }
}
