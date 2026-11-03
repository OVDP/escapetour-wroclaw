<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteQuestStep extends Model
{
    protected $fillable = [
        'game_route_id',
        'quest_step_id',
        'sort_order',
    ];

    public function route()
    {
        return $this->belongsTo(GameRoute::class, 'game_route_id');
    }

    public function questStep()
    {
        return $this->belongsTo(QuestStep::class);
    }
}
