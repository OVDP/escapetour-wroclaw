<?php

namespace App\Http\Requests\Player;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuestAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answer_text' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'submitted_choice' => ['nullable', 'string', 'max:255'],
            'submit_latitude' => ['nullable', 'numeric'],
            'submit_longitude' => ['nullable', 'numeric'],
        ];
    }
}
