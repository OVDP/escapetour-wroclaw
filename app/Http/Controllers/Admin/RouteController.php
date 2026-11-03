<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminMessage;
use App\Models\Game;
use App\Models\GameRoute;
use App\Models\QuestStep;
use App\Models\RouteProgress;
use App\Models\StepSubmission;
use App\Services\Gameplay\StepProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    public function __construct(private readonly StepProgressService $stepProgressService) {}

    public function index()
    {
        $routes = GameRoute::with('game')
            ->with('progress')
            ->withCount('questSteps')
            ->latest()
            ->get();

        $routes->transform(function (GameRoute $group) {
            $total = (int) $group->quest_steps_count;
            $current = (int) ($group->progress?->current_sort_order ?? 1);
            $completed = max(0, min($total, $current - 1));
            $group->progress_percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            return $group;
        });

        return view('admin.routes.index', compact('routes'));
    }

    public function create()
    {
        $game = $this->primaryGame();

        return view('admin.routes.create', compact('game'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $validated['game_id'] = $this->primaryGame()->id;
        $validated['start_assigned'] = false;
        GameRoute::create($validated);

        return redirect()
            ->route('admin.routes.index')
            ->with('success', 'Groep aangemaakt.');
    }

    public function show(GameRoute $route)
    {
        return redirect()->route('admin.routes.edit', $route);
    }

    public function edit(GameRoute $route)
    {
        return view('admin.routes.edit', $this->buildEditPayload($route));
    }

    public function update(Request $request, GameRoute $route)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $route->update($validated);

        return redirect()
            ->route('admin.routes.edit', $route)
            ->with('success', 'Groep bijgewerkt.');
    }

    public function destroy(GameRoute $route)
    {
        $route->delete();

        return redirect()
            ->route('admin.routes.index')
            ->with('success', 'Groep verwijderd.');
    }

    public function updateQuestSteps(Request $request, GameRoute $route)
    {
        return back()->withErrors(['start' => 'Kies een startlocatie; alle opdrachten worden automatisch in rondlopende volgorde gezet.']);
    }

    public function assignStart(Request $request, GameRoute $route)
    {
        $validated = $request->validate(['starting_quest_step_id' => ['required', 'integer']]);
        DB::transaction(function () use ($route, $validated) {
            $route = GameRoute::whereKey($route->id)->lockForUpdate()->firstOrFail();
            $progress = $route->progress()->first();
            if (($progress && ($progress->current_sort_order > 1 || $progress->location_locked_at || $progress->completed_at)) || $route->submissions()->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['start' => 'Deze groep is al begonnen. De startlocatie kan niet meer worden gewijzigd.']);
            }
            $steps = QuestStep::where('game_id', $route->game_id)->orderBy('title')->get();
            $offset = $steps->search(fn ($step) => $step->id === (int) $validated['starting_quest_step_id']);
            if ($offset === false) {
                throw \Illuminate\Validation\ValidationException::withMessages(['start' => 'Kies een locatie uit dit spel.']);
            }
            $ordered = $steps->slice($offset)->concat($steps->take($offset))->values();
            $route->routeQuestSteps()->delete();
            foreach ($ordered as $index => $step) {
                $route->routeQuestSteps()->create(['quest_step_id' => $step->id, 'sort_order' => $index + 1]);
            }
            $route->update(['start_assigned' => true, 'starting_quest_step_id' => $validated['starting_quest_step_id']]);
            RouteProgress::updateOrCreate(['game_route_id' => $route->id], [
                'current_sort_order' => 1, 'location_locked_step_sort_order' => null,
                'location_locked_at' => null, 'location_received_at' => null,
                'timeout_step_sort_order' => null, 'timeout_remaining_seconds' => 0,
            ]);
        });
        return back()->with('success', 'Startlocatie ingesteld. De groep kan beginnen; alle opdrachten volgen in een ronde.');
    }

    public function regenerateToken(GameRoute $route)
    {
        $route->regenerateJoinToken();

        return redirect()
            ->route('admin.routes.edit', $route)
            ->with('success', 'Nieuwe groepstoken gegenereerd. Actieve groepssessies voor deze groep zijn ongeldig gemaakt.');
    }

    public function regenerateAllTokens()
    {
        GameRoute::query()->each(function (GameRoute $route): void {
            $route->regenerateJoinToken();
        });

        return redirect()
            ->route('admin.routes.index')
            ->with('success', 'Alle groepstokens zijn vernieuwd. Actieve groepssessies zijn ongeldig gemaakt.');
    }

    public function live(GameRoute $route): JsonResponse
    {
        $route->loadCount('questSteps');
        $progress = RouteProgress::firstOrCreate(
            ['game_route_id' => $route->id],
            ['current_sort_order' => 1]
        );

        $total = (int) $route->quest_steps_count;
        $completed = max(0, min($total, $progress->current_sort_order - 1));
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        return response()->json([
            'progress_percent' => $percent,
            'completed_steps' => $completed,
            'total_steps' => $total,
            'last_latitude' => $progress->last_latitude,
            'last_longitude' => $progress->last_longitude,
            'last_seen_at' => $progress->last_seen_at?->format('d-m-Y H:i:s'),
            'updated_at' => now()->format('d-m-Y H:i:s'),
        ]);
    }

    public function qr(GameRoute $route)
    {
        $joinUrl = route('join.with-token', $route->join_token);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=800x800&data=' . urlencode($joinUrl);

        return view('admin.routes.qr', compact('route', 'joinUrl', 'qrUrl'));
    }

    public function approveSubmission(Request $request, GameRoute $route, StepSubmission $submission)
    {
        $this->assertSubmissionBelongsToRoute($route, $submission);

        if ($submission->status !== 'pending') {
            return back()->withErrors(['submission' => 'Deze inzending is al verwerkt.']);
        }

        DB::transaction(function () use ($request, $route, $submission): void {
            $submission->update([
                'status' => 'approved',
                'admin_note' => $request->input('admin_note'),
                'reviewed_at' => now(),
            ]);

            $progress = RouteProgress::firstOrCreate(
                ['game_route_id' => $route->id],
                ['current_sort_order' => 1]
            );

            if ($progress->current_sort_order === (int) $submission->step_sort_order) {
                $questStep = $submission->questStep;
                if ($questStep) {
                    $this->stepProgressService->applyReward($progress, $questStep);
                }

                $maxOrder = (int) $route->routeQuestSteps()->max('sort_order');
                $nextOrder = $progress->current_sort_order + 1;

                if ($maxOrder > 0 && $nextOrder > $maxOrder) {
                    $progress->update([
                        'current_sort_order' => $maxOrder + 1,
                        'completed_at' => now(),
                    ]);
                    $this->stepProgressService->markLettersVisibleWhenFinished($route, $progress->refresh());
                } else {
                    $progress->update([
                        'current_sort_order' => $nextOrder,
                        'started_at' => $progress->started_at ?? now(),
                        'location_locked_step_sort_order' => null,
                        'location_locked_latitude' => null,
                        'location_locked_longitude' => null,
                        'location_locked_at' => null,
                    ]);
                }
            }
        });

        return back()->with('success', 'Inzending goedgekeurd en voortgang bijgewerkt.');
    }

    public function rejectSubmission(Request $request, GameRoute $route, StepSubmission $submission)
    {
        $this->assertSubmissionBelongsToRoute($route, $submission);

        if ($submission->status !== 'pending') {
            return back()->withErrors(['submission' => 'Deze inzending is al verwerkt.']);
        }

        $submission->update([
            'status' => 'rejected',
            'admin_note' => $request->input('admin_note'),
            'reviewed_at' => now(),
        ]);

        $questStep = $submission->questStep;
        if ($questStep && (int) $submission->step_sort_order > 0) {
            $progress = RouteProgress::firstOrCreate(
                ['game_route_id' => $route->id],
                ['current_sort_order' => 1]
            );

            $this->stepProgressService->startTimeout(
                $progress,
                (int) $submission->step_sort_order,
                (int) ($questStep->timeout_seconds ?? 30)
            );
        }

        return back()->with('success', 'Inzending afgekeurd.');
    }

    public function postMessage(Request $request, GameRoute $route)
    {
        $validated = $request->validate([
            'audience' => ['required', 'in:group,game,all_active'],
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'expires_at' => ['nullable', 'date'],
        ]);

        AdminMessage::create([
            'audience' => $validated['audience'],
            'title' => $validated['title'] ?? null,
            'message' => $validated['message'],
            'expires_at' => $validated['expires_at'] ?? null,
            'game_id' => in_array($validated['audience'], ['game', 'group'], true) ? $route->game_id : null,
            'game_route_id' => $validated['audience'] === 'group' ? $route->id : null,
        ]);

        return back()->with('success', 'Bericht verstuurd.');
    }

    private function assertSubmissionBelongsToRoute(GameRoute $route, StepSubmission $submission): void
    {
        abort_unless($submission->game_route_id === $route->id, 404);
    }

    private function buildEditPayload(GameRoute $route): array
    {
        $route->load([
            'game',
            'players',
            'questSteps' => fn ($query) => $query->orderBy('route_quest_steps.sort_order'),
        ]);

        $availableQuestSteps = QuestStep::where('game_id', $route->game_id)
            ->orderBy('title')
            ->get();

        $availableStepsForJs = $availableQuestSteps
            ->map(fn (QuestStep $step) => [
                'id' => (int) $step->id,
                'title' => $step->title,
                'step_type' => $step->step_type,
            ])
            ->values()
            ->all();

        $assignedStepIds = $route->questSteps->pluck('id')->all();
        $assignedSteps = $route->questSteps
            ->map(fn (QuestStep $step) => [
                'id' => (int) $step->id,
                'title' => $step->title,
                'step_type' => $step->step_type,
                'sort_order' => (int) $step->pivot->sort_order,
                'start_location_lat' => $step->start_location_lat,
                'start_location_lng' => $step->start_location_lng,
                'start_location_address' => $step->start_location_address,
                'location_lat' => $step->location_lat,
                'location_lng' => $step->location_lng,
                'location_address' => $step->location_address,
            ])
            ->sortBy('sort_order')
            ->values()
            ->all();

        $assignedLocationSteps = collect($assignedSteps)
            ->flatMap(function (array $step): array {
                $entries = [];
                $hasStartLocation = filled($step['start_location_lat']) && filled($step['start_location_lng']);

                if ($hasStartLocation) {
                    $entries[] = [
                        'id' => $step['id'],
                        'title' => $step['title'],
                        'sort_order' => $step['sort_order'],
                        'kind' => 'start',
                        'kind_label' => 'Startlocatie',
                        'lat' => $step['start_location_lat'],
                        'lng' => $step['start_location_lng'],
                        'address' => $step['start_location_address'],
                    ];
                }

                if (filled($step['location_lat']) && filled($step['location_lng'])) {
                    $entries[] = [
                        'id' => $step['id'],
                        'title' => $step['title'],
                        'sort_order' => $step['sort_order'],
                        'kind' => 'submit',
                        'kind_label' => 'Inzendlocatie',
                        'lat' => $step['location_lat'],
                        'lng' => $step['location_lng'],
                        'address' => $step['location_address'],
                    ];
                }

                if (! $hasStartLocation && filled($step['location_lat']) && filled($step['location_lng'])) {
                    $entries[0]['line_anchor'] = true;
                }
                if ($hasStartLocation) {
                    $entries[0]['line_anchor'] = true;
                }

                return $entries;
            })
            ->sortBy([
                ['sort_order', 'asc'],
                ['kind', 'asc'],
            ])
            ->values()
            ->all();

        $progress = RouteProgress::firstOrCreate(
            ['game_route_id' => $route->id],
            ['current_sort_order' => 1]
        );

        $totalSteps = $route->questSteps->count();
        $completedSteps = max(0, min($totalSteps, $progress->current_sort_order - 1));
        $progressPercent = $totalSteps > 0 ? (int) round(($completedSteps / $totalSteps) * 100) : 0;
        $currentStep = $route->questSteps->firstWhere('pivot.sort_order', $progress->current_sort_order);

        $pendingSubmissions = $route->submissions()
            ->with(['player', 'questStep'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $recentSubmissions = $route->submissions()
            ->with(['player', 'questStep'])
            ->where('status', '!=', 'pending')
            ->latest()
            ->limit(20)
            ->get();

        $activeMessages = AdminMessage::query()
            ->where(function ($query) use ($route): void {
                $query->where('audience', 'all_active')
                    ->orWhere(function ($q) use ($route): void {
                        $q->where('audience', 'group')->where('game_route_id', $route->id);
                    })
                    ->orWhere(function ($q) use ($route): void {
                        $q->where('audience', 'game')->where('game_id', $route->game_id);
                    });
            })
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->limit(15)
            ->get();

        return [
            'game' => $this->primaryGame(),
            'route' => $route,
            'availableQuestSteps' => $availableQuestSteps,
            'availableStepsForJs' => $availableStepsForJs,
            'assignedStepIds' => $assignedStepIds,
            'assignedSteps' => $assignedSteps,
            'assignedLocationSteps' => $assignedLocationSteps,
            'progress' => $progress,
            'totalSteps' => $totalSteps,
            'completedSteps' => $completedSteps,
            'progressPercent' => $progressPercent,
            'currentStep' => $currentStep,
            'pendingSubmissions' => $pendingSubmissions,
            'recentSubmissions' => $recentSubmissions,
            'activeMessages' => $activeMessages,
        ];
    }

    private function primaryGame(): Game
    {
        $game = Game::query()->where('id', 1)->first();
        if ($game) {
            return $game;
        }

        $bySlug = Game::query()->where('slug', 'vind-princes-rogieria')->first();
        if ($bySlug) {
            return $bySlug;
        }

        return Game::query()->create([
            'name' => 'Vind Princes Rogieria',
            'slug' => 'vind-princes-rogieria',
            'status' => 'live',
            'case_briefing' => 'Het hof verkeert in onzekerheid. Ontdek wie, waar en wanneer om Princes Rogieria te vinden.',
        ]);
    }
}
