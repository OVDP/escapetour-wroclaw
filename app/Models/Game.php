<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'status',
        'incident_location',
        'incident_at',
        'case_briefing',
        'possible_suspects',
        'possible_locations',
        'story_intro',
        'story_mid',
        'story_final',
    ];

    protected $casts = [
        'incident_at' => 'datetime',
        'possible_suspects' => 'array',
        'possible_locations' => 'array',
    ];

    public function routes()
    {
        return $this->hasMany(GameRoute::class);
    }

    public function questSteps()
    {
        return $this->hasMany(QuestStep::class);
    }

    public function adminMessages()
    {
        return $this->hasMany(AdminMessage::class);
    }
}
