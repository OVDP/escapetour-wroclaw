<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertQuestStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->session()->get('admin_authenticated', false);
    }

    public function rules(): array
    {
        $questionType = (string) $this->input('question_type', 'direct');
        $requiresStartLocation = $this->boolean('requires_location');
        $requiresSubmissionLocation = $questionType === 'location_submit';
        $isMultipleChoice = $questionType === 'multiple_choice';
        $expectsCorrectAnswer = in_array($questionType, ['direct', 'numeric'], true);

        return [
            'title' => ['required', 'string', 'max:255'],
            'story_intro' => ['nullable', 'string'],
            'question_text' => ['required', 'string'],
            'extra_explanation' => ['nullable', 'string'],
            'question_type' => ['required', Rule::in(['direct', 'numeric', 'multiple_choice', 'location_submit'])],
            'correct_answer' => [Rule::requiredIf($expectsCorrectAnswer), 'nullable', 'string', 'max:255'],
            'manual_approval' => ['nullable', 'boolean'],
            'timeout_seconds' => ['nullable', 'integer', 'min:1', 'max:3600'],
            'reward_story_text' => ['nullable', 'string'],
            'reward_letter' => ['nullable', 'string', 'max:10'],
            'reward_suspect_name' => ['nullable', 'string', 'max:255'],
            'accepts_whatsapp_upload' => ['nullable', 'boolean'],

            'requires_location' => ['nullable', 'boolean'],
            'start_location_lat' => [Rule::requiredIf($requiresStartLocation), 'nullable', 'numeric'],
            'start_location_lng' => [Rule::requiredIf($requiresStartLocation), 'nullable', 'numeric'],
            'start_location_address' => [Rule::requiredIf($requiresStartLocation), 'nullable', 'string', 'max:255'],
            'start_location_radius_meters' => [Rule::requiredIf($requiresStartLocation), 'nullable', 'integer', 'min:1', 'max:10000'],
            'show_question_only_when_in_radius' => ['nullable', 'boolean'],
            'location_lat' => [Rule::requiredIf($requiresSubmissionLocation), 'nullable', 'numeric'],
            'location_lng' => [Rule::requiredIf($requiresSubmissionLocation), 'nullable', 'numeric'],
            'location_address' => [Rule::requiredIf($requiresSubmissionLocation), 'nullable', 'string', 'max:255'],
            'location_radius_meters' => [Rule::requiredIf($requiresSubmissionLocation), 'nullable', 'integer', 'min:1', 'max:10000'],
            'multiple_choice_options' => [Rule::requiredIf($isMultipleChoice), 'nullable', 'array', 'min:2'],
            'multiple_choice_options.*' => ['nullable', 'string', 'max:255'],
            'correct_choice' => [Rule::requiredIf($isMultipleChoice), 'nullable', 'string', 'max:255'],
        ];
    }
}
