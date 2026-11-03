<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StepSubmission extends Model
{
    protected $fillable = [
        'game_route_id',
        'route_player_id',
        'quest_step_id',
        'step_sort_order',
        'submitted_answer',
        'photo_path',
        'submitted_choice',
        'submitted_latitude',
        'submitted_longitude',
        'status',
        'admin_note',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function route()
    {
        return $this->belongsTo(GameRoute::class, 'game_route_id');
    }

    public function player()
    {
        return $this->belongsTo(RoutePlayer::class, 'route_player_id');
    }

    public function questStep()
    {
        return $this->belongsTo(QuestStep::class);
    }
}
