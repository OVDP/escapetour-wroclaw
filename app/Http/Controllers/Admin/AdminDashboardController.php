<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameRoute;
use App\Models\QuestStep;
use App\Models\RouteProgress;
use App\Models\StepSubmission;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $groups = $this->buildGroupsOverview();

        return view('admin.dashboard', [
            'gamesCount' => Game::count(),
            'routesCount' => GameRoute::count(),
            'stepsCount' => QuestStep::count(),
            'pendingApprovalsCount' => StepSubmission::where('status', 'pending')->count(),
            'activeGroupsCount' => RouteProgress::whereNotNull('last_seen_at')->count(),
            'groupsOverview' => $groups,
        ]);
    }

    public function live(): JsonResponse
    {
        $groups = $this->buildGroupsOverview();

        return response()->json([
            'groups' => $groups,
            'active_groups_count' => RouteProgress::whereNotNull('last_seen_at')->count(),
            'updated_at' => now()->format('d-m-Y H:i:s'),
        ]);
    }

    private function buildGroupsOverview()
    {
        return GameRoute::with(['game', 'progress'])
            ->withCount('questSteps')
            ->get()
            ->map(function (GameRoute $group): array {
                $total = (int) $group->quest_steps_count;
                $current = (int) ($group->progress?->current_sort_order ?? 1);
                $completed = max(0, min($total, $current - 1));
                $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'start_assigned' => (bool) $group->start_assigned,
                    'game' => $group->game?->name,
                    'progress_percent' => $percent,
                    'last_latitude' => $group->progress?->last_latitude,
                    'last_longitude' => $group->progress?->last_longitude,
                    'last_seen_at' => $group->progress?->last_seen_at?->format('d-m-Y H:i:s'),
                ];
            })
            ->values();
    }
}
