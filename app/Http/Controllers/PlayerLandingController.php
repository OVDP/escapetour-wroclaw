<?php

namespace App\Http\Controllers;

use App\Models\GameRoute;
use Illuminate\View\View;

class PlayerLandingController extends Controller
{
    public function index(\Illuminate\Http\Request $request): View
    {
        return view('player.landing', [
            'ownGroup' => GameRoute::where('creator_device_hash', $request->attributes->get('device_hash'))->first(),
            'gameAvailable' => \App\Models\Game::where('slug', 'red-de-zabka-kikker')->where('status', 'live')->exists(),
        ]);
    }

    public function join(string $token): View
    {
        $route = GameRoute::with('game')
            ->where('join_token', strtoupper($token))
            ->first();

        return view('player.join', [
            'route' => $route,
            'token' => strtoupper($token),
        ]);
    }
}
