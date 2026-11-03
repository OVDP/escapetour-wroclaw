<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminMessage extends Model
{
    protected $fillable = [
        'game_id',
        'game_route_id',
        'audience',
        'title',
        'message',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function group()
    {
        return $this->belongsTo(GameRoute::class, 'game_route_id');
    }
}
