<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GameRoute extends Model
{
    protected $fillable = [
        'creator_device_hash', 'start_assigned', 'starting_quest_step_id',
        'game_id',
        'name',
        'description',
        'join_token',
    ];

    protected $casts = ['start_assigned' => 'boolean'];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function questSteps()
    {
        return $this->belongsToMany(QuestStep::class, 'route_quest_steps')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('route_quest_steps.sort_order');
    }

    public function routeQuestSteps()
    {
        return $this->hasMany(RouteQuestStep::class);
    }

    public function progress()
    {
        return $this->hasOne(RouteProgress::class, 'game_route_id');
    }

    public function players()
    {
        return $this->hasMany(RoutePlayer::class, 'game_route_id');
    }

    public function submissions()
    {
        return $this->hasMany(StepSubmission::class, 'game_route_id');
    }

    public function adminMessages()
    {
        return $this->hasMany(AdminMessage::class, 'game_route_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $route): void {
            if (blank($route->join_token)) {
                $route->join_token = self::generateUniqueJoinToken();
            }
        });
    }

    public static function generateUniqueJoinToken(): string
    {
        do {
            $token = strtoupper(Str::random(10));
        } while (self::where('join_token', $token)->exists());

        return $token;
    }

    public function regenerateJoinToken(): void
    {
        $this->forceFill([
            'join_token' => self::generateUniqueJoinToken(),
        ])->save();
    }
}
