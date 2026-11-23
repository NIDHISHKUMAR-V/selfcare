<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WaterLogRequest extends FormRequest
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
            'amount_ml' => ['required', 'integer', 'min:1', 'max:5000'],
            'log_date' => ['required', 'date'],
            'log_time' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
