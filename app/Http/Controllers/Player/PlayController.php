<?php

namespace App\Http\Controllers\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\SubmitQuestAnswerRequest;
use App\Models\AdminMessage;
use App\Models\GameRoute;
use App\Models\QuestStep;
use App\Models\RouteProgress;
use App\Models\RoutePlayer;
use App\Models\StepSubmission;
use App\Services\Gameplay\StepProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayController extends Controller
{
    public function __construct(private readonly StepProgressService $stepProgressService) {}

    public function join(Request $request, string $token): RedirectResponse
    {
        $group = GameRoute::where('join_token', strtoupper($token))->first();

        if (! $group) {
            return back()->withErrors(['token' => 'Deze groepslink is ongeldig of verlopen.']);
        }

        $playerId = (int) $request->session()->get('player_id', 0);
        if (! $playerId || ! RoutePlayer::whereKey($playerId)->where('game_route_id', $group->id)->exists()) {
            $nextIndex = RoutePlayer::where('game_route_id', $group->id)->count() + 1;
            $player = RoutePlayer::create([
                'game_route_id' => $group->id,
                'name' => 'Lid ' . $nextIndex,
                'last_seen_at' => now(),
            ]);
            $playerId = (int) $player->id;
        }

        RouteProgress::firstOrCreate(
            ['game_route_id' => $group->id],
            ['current_sort_order' => 1, 'started_at' => now()]
        );

        $request->session()->put([
            'player_route_id' => $group->id,
            'player_join_token' => $group->join_token,
            'player_id' => $playerId,
        ]);

        return redirect()->route('play.dashboard');
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        $group = $this->resolveGroupSession($request);
        if (! $group) {
            return redirect()->route('landing')->with('error', 'Je groepssessie is verlopen. Scan opnieuw de QR-code.');
        }

        if (! $group->start_assigned) {
            return view('player.waiting', ['group' => $group]);
        }

        $group->load([
            'game',
            'questSteps' => fn ($query) => $query->orderBy('route_quest_steps.sort_order'),
        ]);

        $progress = $this->progress($group);
        $stepCount = $group->questSteps->count();
        if ($stepCount > 0 && (int) $progress->current_sort_order > $stepCount && ! $progress->show_letters) {
            $this->stepProgressService->markLettersVisibleWhenFinished($group, $progress);
            $progress = $progress->refresh();
        }

        $currentStep = $group->questSteps->firstWhere('pivot.sort_order', $progress->current_sort_order);
        $completedCount = max(0, min($stepCount, $progress->current_sort_order - 1));
        $progressPercent = $stepCount > 0 ? (int) round(($completedCount / $stepCount) * 100) : 0;
        $isFinished = $currentStep === null && $stepCount > 0;
        $startLocation = $this->resolveStartLocation($currentStep);

        $distanceMeters = null;
        $isInRadius = false;
        if ($currentStep && $startLocation !== null && isset($progress->last_latitude, $progress->last_longitude)) {
            $distanceMeters = (int) round($this->stepProgressService->distanceMeters(
                (float) $progress->last_latitude,
                (float) $progress->last_longitude,
                (float) $startLocation['lat'],
                (float) $startLocation['lng']
            ));
            $isInRadius = $distanceMeters <= (int) $startLocation['radius'];
        }

        $locationLocked = $currentStep && $this->hasConfirmedLocation($progress, $currentStep);

        $latestPendingSubmission = StepSubmission::query()
            ->where('game_route_id', $group->id)
            ->where('step_sort_order', $progress->current_sort_order)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $messages = AdminMessage::query()
            ->where(function ($query) use ($group): void {
                $query->where('audience', 'all_active')
                    ->orWhere(function ($q) use ($group): void {
                        $q->where('audience', 'group')->where('game_route_id', $group->id);
                    })
                    ->orWhere(function ($q) use ($group): void {
                        $q->where('audience', 'game')->where('game_id', $group->game_id);
                    });
            })
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->limit(10)
            ->get();

        $activeTimeout = (int) ($progress->timeout_remaining_seconds ?? 0) > 0
            && (int) ($progress->timeout_step_sort_order ?? 0) === (int) $progress->current_sort_order;

        $storyEntries = $progress->unlocked_story_entries ?? [];
        $unlockedSuspects = $progress->unlocked_suspects ?? [];
        $hiddenLetters = $progress->hidden_collected_letters ?? [];
        $visibleLetters = $progress->show_letters ? array_map(fn ($item) => $item['letter'] ?? '', $hiddenLetters) : [];

        return view('player.play', [
            'group' => $group,
            'progress' => $progress,
            'currentStep' => $currentStep,
            'stepCount' => $stepCount,
            'progressPercent' => $progressPercent,
            'isFinished' => $isFinished,
            'latestPendingSubmission' => $latestPendingSubmission,
            'messages' => $messages,
            'distanceMeters' => $distanceMeters,
            'isInRadius' => $isInRadius,
            'locationLocked' => $locationLocked,
            'startLocationAddress' => $startLocation['address'] ?? null,
            'activeTimeout' => $activeTimeout,
            'timeoutRemainingSeconds' => (int) ($progress->timeout_remaining_seconds ?? 0),
            'timeoutPausedState' => (bool) ($progress->timeout_paused_state ?? true),
            'storyEntries' => $storyEntries,
            'unlockedSuspects' => $unlockedSuspects,
            'hiddenLettersCount' => count($hiddenLetters),
            'visibleLetters' => $visibleLetters,
            'locationEnabled' => isset($progress->last_latitude, $progress->last_longitude),
        ]);
    }

    public function submitRedirect(Request $request): RedirectResponse
    {
        $group = $this->resolveGroupSession($request);
        if (! $group) {
            return redirect()->route('landing')->with('error', 'Je groepssessie is verlopen. Scan opnieuw de QR-code.');
        }

        return redirect()->route('play.dashboard', ['task' => 1]);
    }

    public function submit(SubmitQuestAnswerRequest $request): RedirectResponse
    {
        $group = $this->resolveGroupSession($request);
        if (! $group) {
            return redirect()->route('landing')->with('error', 'Je groepssessie is verlopen. Scan opnieuw de QR-code.');
        }

        if (! $group->start_assigned) {
            return redirect()->route('play.dashboard');
        }
        $group->load('questSteps');
        $progress = $this->progress($group);
        $currentStep = $group->questSteps->firstWhere('pivot.sort_order', $progress->current_sort_order);

        if (! $currentStep) {
            return redirect()->route('play.dashboard');
        }

        if ((int) ($progress->timeout_step_sort_order ?? 0) === (int) $progress->current_sort_order && (int) ($progress->timeout_remaining_seconds ?? 0) > 0) {
            return back()->withErrors(['timeout' => 'Nog even wachten: timeout actief.']);
        }

        $pendingForCurrentStep = StepSubmission::query()
            ->where('game_route_id', $group->id)
            ->where('step_sort_order', (int) $progress->current_sort_order)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $validatedInput = $request->validated();
        $answerText = trim((string) ($validatedInput['answer_text'] ?? ''));
        $submittedChoice = trim((string) ($validatedInput['submitted_choice'] ?? ''));
        $submitLatitude = isset($validatedInput['submit_latitude']) ? (float) $validatedInput['submit_latitude'] : null;
        $submitLongitude = isset($validatedInput['submit_longitude']) ? (float) $validatedInput['submit_longitude'] : null;
        $manualReview = $currentStep->requiresManualApproval();
        $questionType = (string) ($currentStep->question_type ?? 'direct');
        $startLocation = $this->resolveStartLocation($currentStep);
        $isNumericType = $questionType === 'numeric'
            || ($questionType === 'direct' && $this->stepProgressService->normalizeNumericAnswer((string) ($currentStep->correct_answer ?? '')) !== '');
        $isMultipleChoice = $questionType === 'multiple_choice';
        $isLocationSubmission = $questionType === 'location_submit' || (bool) ($currentStep->location_submission_required ?? false);

        if ($submitLatitude !== null && $submitLongitude !== null) {
            $progress->update([
                'last_latitude' => $submitLatitude,
                'last_longitude' => $submitLongitude,
                'last_seen_at' => now(),
            ]);
            $playerId = (int) $request->session()->get('player_id', 0);
            if ($playerId > 0) {
                RoutePlayer::whereKey($playerId)->where('game_route_id', $group->id)->update([
                    'last_latitude' => $submitLatitude,
                    'last_longitude' => $submitLongitude,
                    'last_seen_at' => now(),
                ]);
            }
            $progress = $progress->refresh();
        }

        if (! $this->hasConfirmedLocation($progress, $currentStep)) {
            return back()->withErrors(['location' => 'Ga naar de locatie en bevestig eerst je actuele GPS-locatie.']);
        }

        if ($manualReview) {
            if ($pendingForCurrentStep) {
                return back()->with('success', 'Je inzending is al verstuurd en wacht op goedkeuring van de spelleiding.');
            }

            if ($currentStep->step_type === 'photo_upload' && ! $request->hasFile('photo')) {
                return back()->withErrors(['photo' => 'Voeg een foto toe voor deze opdracht.'])->withInput();
            }
            if ($answerText === '' && $currentStep->step_type === 'photo_upload') {
                return back()->withErrors(['answer_text' => 'Voeg een toelichting toe; bij het ballet ook de naam.'])->withInput();
            }
            StepSubmission::create([
                'photo_path' => $request->hasFile('photo') ? $request->file('photo')->store('submissions', 'local') : null,
                'game_route_id' => $group->id,
                'route_player_id' => (int) $request->session()->get('player_id'),
                'quest_step_id' => $currentStep->id,
                'step_sort_order' => (int) $currentStep->pivot->sort_order,
                'submitted_answer' => $answerText,
                'submitted_choice' => $submittedChoice !== '' ? $submittedChoice : null,
                'submitted_latitude' => $progress->last_latitude,
                'submitted_longitude' => $progress->last_longitude,
                'status' => 'pending',
            ]);

            return back()->with('success', 'Inzending verzonden. Wacht op goedkeuring van de spelleiding.');
        }

        if ($isMultipleChoice && $submittedChoice === '') {
            return back()->withErrors(['answer' => 'Kies een optie voordat je verstuurt.']);
        }

        if (! $isLocationSubmission && ! $isMultipleChoice && $answerText === '') {
            return back()->withErrors(['answer' => 'Vul een antwoord in.']);
        }

        if ($isLocationSubmission) {
            if (! isset($progress->last_latitude, $progress->last_longitude) || ! $this->stepProgressService->isWithinStepLocation($currentStep, (float) $progress->last_latitude, (float) $progress->last_longitude)) {
                StepSubmission::create([
                    'game_route_id' => $group->id,
                    'route_player_id' => (int) $request->session()->get('player_id'),
                    'quest_step_id' => $currentStep->id,
                    'step_sort_order' => (int) $currentStep->pivot->sort_order,
                    'submitted_answer' => '[locatie fout]',
                    'submitted_latitude' => $progress->last_latitude,
                    'submitted_longitude' => $progress->last_longitude,
                    'status' => 'auto_failed',
                ]);

                $timeoutSeconds = (int) ($currentStep->timeout_seconds ?? 30);
                $this->stepProgressService->startTimeout($progress, (int) $progress->current_sort_order, $timeoutSeconds);

                return back()->withErrors(['timeout' => "Je hebt niet de juiste locatie ingezonden. Je mag over {$timeoutSeconds} seconden een nieuwe locatie inzenden."]);
            }
            $isCorrect = true;
        } elseif ($isMultipleChoice) {
            $isCorrect = $this->stepProgressService->normalizeAnswer($submittedChoice)
                === $this->stepProgressService->normalizeAnswer((string) ($currentStep->correct_choice ?? ''));
        } elseif ($isNumericType) {
            $isCorrect = $this->stepProgressService->acceptsAnswer($currentStep, $answerText, true);
        } else {
            $isCorrect = $this->stepProgressService->acceptsAnswer($currentStep, $answerText);
        }

        StepSubmission::create([
            'game_route_id' => $group->id,
            'route_player_id' => (int) $request->session()->get('player_id'),
            'quest_step_id' => $currentStep->id,
            'step_sort_order' => (int) $currentStep->pivot->sort_order,
            'submitted_answer' => $isLocationSubmission ? '[locatie ingezonden]' : $answerText,
            'submitted_choice' => $submittedChoice !== '' ? $submittedChoice : null,
            'submitted_latitude' => $progress->last_latitude,
            'submitted_longitude' => $progress->last_longitude,
            'status' => $isCorrect ? 'auto_passed' : 'auto_failed',
        ]);

        if (! $isCorrect) {
            $timeoutSeconds = (int) ($currentStep->timeout_seconds ?? 30);
            $this->stepProgressService->startTimeout($progress, (int) $progress->current_sort_order, $timeoutSeconds);

            return back()->withErrors(['timeout' => "Je antwoord is niet juist. Je mag over {$timeoutSeconds} seconden opnieuw proberen."]);
        }

        $this->stepProgressService->applyReward($progress, $currentStep);
        $this->advanceProgress($group, $progress);

        return redirect()->route('play.dashboard')->with('success', 'Goed antwoord. Volgende stap vrijgespeeld.')->with('celebrate_success', true);
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $group = $this->resolveGroupSession($request);
        if (! $group) {
            return response()->json(['ok' => false], 401);
        }

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $progress = $this->progress($group);
        $progress->update([
            'location_received_at' => now(),
            'last_latitude' => $validated['latitude'],
            'last_longitude' => $validated['longitude'],
            'last_seen_at' => now(),
        ]);

        $playerId = (int) $request->session()->get('player_id', 0);
        if ($playerId > 0) {
            RoutePlayer::whereKey($playerId)->where('game_route_id', $group->id)->update([
                'last_latitude' => $validated['latitude'],
                'last_longitude' => $validated['longitude'],
                'last_seen_at' => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function lockLocation(Request $request): JsonResponse
    {
        $group = $this->resolveGroupSession($request);
        if (! $group) {
            return response()->json(['ok' => false], 401);
        }

        $group->load('questSteps');
        $progress = $this->progress($group);
        $currentStep = $group->questSteps->firstWhere('pivot.sort_order', $progress->current_sort_order);
        $startLocation = $this->resolveStartLocation($currentStep);

        if (! $group->start_assigned || ! $currentStep || $startLocation === null) {
            return response()->json(['ok' => false, 'message' => 'Geen locatie-opdracht actief.'], 422);
        }

        if (! isset($progress->last_latitude, $progress->last_longitude) || ! $progress->location_received_at || $progress->location_received_at->lt(now()->subMinutes(2))) {
            return response()->json(['ok' => false, 'message' => 'Nog geen actuele locatie beschikbaar.'], 422);
        }

        $distance = $this->stepProgressService->distanceMeters(
            (float) $progress->last_latitude,
            (float) $progress->last_longitude,
            (float) $startLocation['lat'],
            (float) $startLocation['lng']
        );
        $isWithin = $distance <= (float) $startLocation['radius'];
        if (! $isWithin) {
            return response()->json(['ok' => false, 'message' => 'Je bent nog niet binnen de ingestelde radius.'], 422);
        }

        $progress->update([
            'location_locked_step_sort_order' => (int) $progress->current_sort_order,
            'location_locked_latitude' => $progress->last_latitude,
            'location_locked_longitude' => $progress->last_longitude,
            'location_locked_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function tickTimeout(Request $request): JsonResponse
    {
        $group = $this->resolveGroupSession($request);
        if (! $group) {
            return response()->json(['ok' => false], 401);
        }

        $progress = $this->progress($group);
        if ((int) ($progress->timeout_step_sort_order ?? 0) !== (int) $progress->current_sort_order) {
            return response()->json([
                'ok' => true,
                'active_timeout' => false,
                'remaining_seconds' => 0,
                'paused' => true,
            ]);
        }

        $progress = $this->stepProgressService->tickTimeout($progress);

        return response()->json([
            'ok' => true,
            'active_timeout' => (int) $progress->timeout_remaining_seconds > 0,
            'remaining_seconds' => (int) $progress->timeout_remaining_seconds,
            'paused' => (bool) $progress->timeout_paused_state,
        ]);
    }

    public function pauseTimeout(Request $request): JsonResponse
    {
        $group = $this->resolveGroupSession($request);
        if (! $group) {
            return response()->json(['ok' => false], 401);
        }

        $progress = $this->progress($group);
        $progress = $this->stepProgressService->pauseTimeout($progress);

        return response()->json([
            'ok' => true,
            'active_timeout' => (int) $progress->timeout_remaining_seconds > 0,
            'remaining_seconds' => (int) $progress->timeout_remaining_seconds,
            'paused' => (bool) $progress->timeout_paused_state,
        ]);
    }

    public function live(Request $request): JsonResponse
    {
        $group = $this->resolveGroupSession($request);
        if (! $group) {
            return response()->json(['ok' => false], 401);
        }

        $group->load('questSteps');
        $progress = $this->progress($group);
        $currentStep = $group->questSteps->firstWhere('pivot.sort_order', $progress->current_sort_order);
        $startLocation = $this->resolveStartLocation($currentStep);

        $distanceMeters = null;
        $isInRadius = false;
        if ($currentStep && $startLocation !== null && isset($progress->last_latitude, $progress->last_longitude)) {
            $distanceMeters = (int) round($this->stepProgressService->distanceMeters(
                (float) $progress->last_latitude,
                (float) $progress->last_longitude,
                (float) $startLocation['lat'],
                (float) $startLocation['lng']
            ));
            $isInRadius = $distanceMeters <= (int) $startLocation['radius'];
        }

        $pendingCurrentCount = StepSubmission::query()
            ->where('game_route_id', $group->id)
            ->where('step_sort_order', $progress->current_sort_order)
            ->where('status', 'pending')
            ->count();

        $latestReviewed = StepSubmission::query()
            ->with('questStep')
            ->where('game_route_id', $group->id)
            ->whereNotNull('reviewed_at')
            ->latest('reviewed_at')
            ->first();

        $messages = AdminMessage::query()
            ->where(function ($query) use ($group): void {
                $query->where('audience', 'all_active')
                    ->orWhere(function ($q) use ($group): void {
                        $q->where('audience', 'group')->where('game_route_id', $group->id);
                    })
                    ->orWhere(function ($q) use ($group): void {
                        $q->where('audience', 'game')->where('game_id', $group->game_id);
                    });
            })
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'message']);

        return response()->json([
            'ok' => true,
            'current_sort_order' => (int) $progress->current_sort_order,
            'pending_current_count' => $pendingCurrentCount,
            'latest_reviewed' => $latestReviewed ? [
                'id' => $latestReviewed->id,
                'status' => $latestReviewed->status,
                'step_sort_order' => (int) $latestReviewed->step_sort_order,
                'step_title' => $latestReviewed->questStep?->title ?? 'Opdracht',
            ] : null,
            'is_in_radius' => $isInRadius,
            'distance_meters' => $distanceMeters,
            'location_locked' => $currentStep && $this->hasConfirmedLocation($progress, $currentStep),
            'start_assigned' => (bool) $group->start_assigned,
            'active_timeout' => (int) ($progress->timeout_remaining_seconds ?? 0) > 0 && (int) ($progress->timeout_step_sort_order ?? 0) === (int) $progress->current_sort_order,
            'remaining_timeout_seconds' => (int) ($progress->timeout_remaining_seconds ?? 0),
            'timeout_paused_state' => (bool) ($progress->timeout_paused_state ?? true),
            'messages' => $messages,
        ]);
    }

    public function leave(Request $request): RedirectResponse
    {
        $request->session()->forget(['player_route_id', 'player_join_token', 'player_id']);

        return redirect()->route('landing');
    }

    private function resolveGroupSession(Request $request): ?GameRoute
    {
        $groupId = (int) $request->session()->get('player_route_id');
        $sessionJoinToken = (string) $request->session()->get('player_join_token', '');

        if (! $groupId) {
            return null;
        }

        $group = GameRoute::find($groupId);
        if (! $group) {
            return null;
        }

        if ($sessionJoinToken === '' || $sessionJoinToken !== $group->join_token) {
            $request->session()->forget(['player_route_id', 'player_join_token', 'player_id']);

            return null;
        }

        return $group;
    }

    private function progress(GameRoute $group): RouteProgress
    {
        return RouteProgress::firstOrCreate(
            ['game_route_id' => $group->id],
            ['current_sort_order' => 1, 'started_at' => now()]
        );
    }

    private function advanceProgress(GameRoute $group, RouteProgress $progress): void
    {
        $maxOrder = (int) $group->routeQuestSteps()->max('sort_order');
        $nextOrder = $progress->current_sort_order + 1;

        if ($maxOrder > 0 && $nextOrder > $maxOrder) {
            $progress->update([
                'current_sort_order' => $maxOrder + 1,
                'completed_at' => now(),
                'started_at' => $progress->started_at ?? now(),
                'location_locked_step_sort_order' => null,
                'location_locked_latitude' => null,
                'location_locked_longitude' => null,
                'location_locked_at' => null,
            ]);

            $this->stepProgressService->markLettersVisibleWhenFinished($group, $progress->refresh());

            return;
        }

        $progress->update([
            'current_sort_order' => $nextOrder,
            'started_at' => $progress->started_at ?? now(),
            'location_locked_step_sort_order' => null,
            'location_locked_latitude' => null,
            'location_locked_longitude' => null,
            'location_locked_at' => null,
            'timeout_step_sort_order' => null,
            'timeout_remaining_seconds' => 0,
            'timeout_last_tick_at' => now(),
            'timeout_paused_state' => true,
        ]);
    }

    private function resolveStartLocation(?QuestStep $step): ?array
    {
        if (! $step) {
            return null;
        }

        if (
            isset($step->start_location_lat, $step->start_location_lng, $step->start_location_radius_meters)
            && (float) $step->start_location_radius_meters > 0
        ) {
            return [
                'lat' => (float) $step->start_location_lat,
                'lng' => (float) $step->start_location_lng,
                'radius' => (int) $step->start_location_radius_meters,
                'address' => $this->compactAddress((string) ($step->start_location_address ?? '')),
            ];
        }

        $questionType = (string) ($step->question_type ?? '');
        if ($this->stepProgressService->hasStepLocationConfig($step)) {
            return [
                'lat' => (float) $step->location_lat,
                'lng' => (float) $step->location_lng,
                'radius' => (int) $step->location_radius_meters,
                'address' => $this->compactAddress((string) ($step->location_address ?? '')),
            ];
        }

        return null;
    }

    private function hasConfirmedLocation(RouteProgress $progress, QuestStep $step): bool
    {
        $location = $this->resolveStartLocation($step);
        return $location !== null
            && (int) $progress->location_locked_step_sort_order === (int) $progress->current_sort_order
            && $progress->location_received_at?->gte(now()->subMinutes(2))
            && isset($progress->last_latitude, $progress->last_longitude)
            && $this->stepProgressService->distanceMeters((float) $progress->last_latitude, (float) $progress->last_longitude, $location['lat'], $location['lng']) <= $location['radius'];
    }

    private function compactAddress(string $address): string
    {
        $parts = array_values(array_filter(array_map('trim', explode(',', $address))));

        if ($parts === []) {
            return $address;
        }

        return implode(', ', array_slice($parts, 0, 3));
    }
}
