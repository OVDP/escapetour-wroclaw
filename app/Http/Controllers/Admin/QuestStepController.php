<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertQuestStepRequest;
use App\Models\Game;
use App\Models\QuestStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class QuestStepController extends Controller
{
    public function index(): View
    {
        $questSteps = QuestStep::with('game')
            ->withCount('routes')
            ->orderBy('title')
            ->get();

        return view('admin.quest-steps.index', compact('questSteps'));
    }

    public function create(): View
    {
        $game = $this->primaryGame();
        $quest_step = null;

        return view('admin.quest-steps.create', compact('game', 'quest_step'));
    }

    public function store(UpsertQuestStepRequest $request): RedirectResponse
    {
        QuestStep::create($this->payloadFromRequest($request));

        return redirect()
            ->route('admin.quest-steps.index')
            ->with('success', 'Opdracht aangemaakt.');
    }

    public function show(QuestStep $quest_step): View
    {
        $quest_step->load([
            'game',
            'routes' => fn ($query) => $query->orderBy('route_quest_steps.sort_order'),
        ]);

        return view('admin.quest-steps.show', compact('quest_step'));
    }

    public function edit(QuestStep $quest_step): View
    {
        $game = $this->primaryGame();

        return view('admin.quest-steps.edit', compact('quest_step', 'game'));
    }

    public function update(UpsertQuestStepRequest $request, QuestStep $quest_step): RedirectResponse
    {
        $quest_step->update($this->payloadFromRequest($request));

        return redirect()
            ->route('admin.quest-steps.index')
            ->with('success', 'Opdracht bijgewerkt.');
    }

    public function destroy(QuestStep $quest_step): RedirectResponse
    {
        $quest_step->delete();

        return redirect()
            ->route('admin.quest-steps.index')
            ->with('success', 'Opdracht verwijderd.');
    }

    private function payloadFromRequest(UpsertQuestStepRequest $request): array
    {
        $validated = $request->validated();

        $questionType = $validated['question_type'] ?? 'direct';
        $requiresStartLocation = $request->boolean('requires_location');
        $manualApproval = $request->boolean('manual_approval');
        $multipleChoiceOptions = collect($validated['multiple_choice_options'] ?? [])
            ->map(fn ($option) => trim((string) $option))
            ->filter()
            ->values()
            ->all();
        $correctChoice = isset($validated['correct_choice']) ? trim((string) $validated['correct_choice']) : null;

        if ($questionType === 'multiple_choice' && $correctChoice !== null && ! in_array($correctChoice, $multipleChoiceOptions, true)) {
            $multipleChoiceOptions[] = $correctChoice;
        }

        $stepType = match ($questionType) {
            'multiple_choice' => 'multiple_choice',
            'location_submit' => 'location_check',
            default => $manualApproval ? 'manual_approval' : 'text_answer',
        };

        $payload = [
            'game_id' => $this->primaryGame()->id,
            'title' => $validated['title'],
            'story_intro' => $validated['story_intro'] ?? null,
            'question_text' => $validated['question_text'],
            'extra_explanation' => $validated['extra_explanation'] ?? null,
            'question_type' => $questionType,
            'correct_answer' => $validated['correct_answer'] ?? null,
            'manual_approval' => $manualApproval,
            'manual_approval_required' => $manualApproval,
            'timeout_seconds' => (int) ($validated['timeout_seconds'] ?? 30),
            'cooldown_seconds' => (int) ($validated['timeout_seconds'] ?? 30),
            'reward_story_text' => $validated['reward_story_text'] ?? null,
            'reward_letter' => isset($validated['reward_letter']) && $validated['reward_letter'] !== ''
                ? mb_substr((string) $validated['reward_letter'], 0, 1)
                : null,
            'reward_suspect_name' => isset($validated['reward_suspect_name']) && trim((string) $validated['reward_suspect_name']) !== ''
                ? trim((string) $validated['reward_suspect_name'])
                : null,
            'requires_location' => $requiresStartLocation,
            'start_location_lat' => $requiresStartLocation ? ($validated['start_location_lat'] ?? null) : null,
            'start_location_lng' => $requiresStartLocation ? ($validated['start_location_lng'] ?? null) : null,
            'start_location_address' => $requiresStartLocation ? $this->compactAddress($validated['start_location_address'] ?? null) : null,
            'start_location_radius_meters' => $requiresStartLocation ? ($validated['start_location_radius_meters'] ?? null) : null,
            'show_question_only_when_in_radius' => $requiresStartLocation ? $request->boolean('show_question_only_when_in_radius') : false,
            'location_lat' => $questionType === 'location_submit' ? ($validated['location_lat'] ?? null) : null,
            'location_lng' => $questionType === 'location_submit' ? ($validated['location_lng'] ?? null) : null,
            'location_address' => $questionType === 'location_submit' ? $this->compactAddress($validated['location_address'] ?? null) : null,
            'location_radius_meters' => $questionType === 'location_submit' ? ($validated['location_radius_meters'] ?? null) : null,
            'accepts_whatsapp_upload' => $request->boolean('accepts_whatsapp_upload'),
            'location_submission_required' => $questionType === 'location_submit',
            'multiple_choice_json' => $questionType === 'multiple_choice' ? $multipleChoiceOptions : null,
            'correct_choice' => $questionType === 'multiple_choice' ? $correctChoice : null,

            // Legacy compatibility fields
            'step_type' => $stepType,
            'answer_text' => $validated['correct_answer'] ?? null,
            'latitude' => $questionType === 'location_submit' ? ($validated['location_lat'] ?? null) : null,
            'longitude' => $questionType === 'location_submit' ? ($validated['location_lng'] ?? null) : null,
            'radius_meters' => $questionType === 'location_submit' ? ($validated['location_radius_meters'] ?? null) : null,
            'story_text' => $validated['story_intro'] ?? null,
            'clue_reveal_text' => $validated['reward_story_text'] ?? null,
        ];

        return $payload;
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

    private function compactAddress(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $value))));
        if ($parts === []) {
            return null;
        }

        return implode(', ', array_slice($parts, 0, 3));
    }
}
