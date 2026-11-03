<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteProgress extends Model
{
    protected $table = 'route_progress';

    protected $fillable = [
        'game_route_id',
        'current_sort_order',
        'started_at',
        'completed_at',
        'last_latitude',
        'last_longitude',
        'last_seen_at',
        'location_received_at',
        'unlocked_story_entries',
        'unlocked_suspects',
        'hidden_collected_letters',
        'show_letters',
        'timeout_step_sort_order',
        'timeout_remaining_seconds',
        'timeout_last_tick_at',
        'timeout_paused_state',
        'location_locked_step_sort_order',
        'location_locked_latitude',
        'location_locked_longitude',
        'location_locked_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'location_received_at' => 'datetime',
        'unlocked_story_entries' => 'array',
        'unlocked_suspects' => 'array',
        'hidden_collected_letters' => 'array',
        'show_letters' => 'boolean',
        'timeout_last_tick_at' => 'datetime',
        'timeout_paused_state' => 'boolean',
        'location_locked_at' => 'datetime',
    ];

    public function route()
    {
        return $this->belongsTo(GameRoute::class, 'game_route_id');
    }
}
