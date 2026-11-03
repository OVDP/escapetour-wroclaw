<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestStep extends Model
{
    protected $fillable = [
        'game_id',
        'question_image', 'acceptable_answers', 'review_notes',
        'title',
        'story_intro',
        'question_text',
        'extra_explanation',
        'question_type',
        'correct_answer',
        'manual_approval',
        'timeout_seconds',
        'reward_story_text',
        'reward_letter',
        'reward_suspect_name',
        'story_text',
        'clue_category',
        'clue_reveal_text',
        'step_type',
        'requires_location',
        'location_lat',
        'location_lng',
        'location_address',
        'location_radius_meters',
        'show_question_only_when_in_radius',
        'start_location_lat',
        'start_location_lng',
        'start_location_address',
        'start_location_radius_meters',
        'accepts_whatsapp_upload',
        'location_submission_required',
        'latitude',
        'longitude',
        'radius_meters',
        'answer_text',
        'multiple_choice_json',
        'correct_choice',
        'cooldown_seconds',
        'manual_approval_required',
    ];

    protected $casts = [
        'acceptable_answers' => 'array',
        'requires_location' => 'boolean',
        'manual_approval_required' => 'boolean',
        'manual_approval' => 'boolean',
        'show_question_only_when_in_radius' => 'boolean',
        'accepts_whatsapp_upload' => 'boolean',
        'location_submission_required' => 'boolean',
        'multiple_choice_json' => 'array',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function routes()
    {
        return $this->belongsToMany(GameRoute::class, 'route_quest_steps')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function routeQuestSteps()
    {
        return $this->hasMany(RouteQuestStep::class);
    }

    public function submissions()
    {
        return $this->hasMany(StepSubmission::class);
    }

    public function requiresManualApproval(): bool
    {
        return $this->manual_approval || $this->manual_approval_required || $this->step_type === 'manual_approval';
    }

    public function canBeAutoChecked(): bool
    {
        return ! $this->requiresManualApproval();
    }
}
