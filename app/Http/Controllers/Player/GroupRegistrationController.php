<?php
namespace App\Http\Controllers\Player;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameRoute;
use Illuminate\Http\Request;
class GroupRegistrationController extends Controller {
    public function store(Request $request) {
        $validated = $request->validate(['group_name' => ['required', 'string', 'max:100']]);
        $game = Game::where('slug', 'red-de-zabka-kikker')->where('status', 'live')->firstOrFail();
        // A unique database key also prevents duplicate groups on repeated submissions.
        $group = GameRoute::firstOrCreate(['creator_device_hash' => $request->attributes->get('device_hash')], [
            'game_id' => $game->id, 'name' => $validated['group_name'], 'start_assigned' => false,
        ]);
        return app(PlayController::class)->join($request, $group->join_token);
    }
}
