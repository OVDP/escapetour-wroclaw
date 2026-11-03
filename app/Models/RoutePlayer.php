<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutePlayer extends Model
{
    protected $fillable = [
        'game_route_id',
        'name',
        'last_latitude',
        'last_longitude',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function route()
    {
        return $this->belongsTo(GameRoute::class, 'game_route_id');
    }

    public function submissions()
    {
        return $this->hasMany(StepSubmission::class);
    }
}
