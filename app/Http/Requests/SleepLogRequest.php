<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SleepLogRequest extends FormRequest
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
            'sleep_date' => ['required', 'date'],
            'sleep_time' => ['required', 'date_format:Y-m-d\TH:i'],
            'wake_time' => ['required', 'date_format:Y-m-d\TH:i'],
            'quality_rating' => ['required', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
