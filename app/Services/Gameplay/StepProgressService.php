<?php

namespace App\Services\Gameplay;

use App\Models\GameRoute;
use App\Models\QuestStep;
use App\Models\RouteProgress;

class StepProgressService
{
    public function normalizeAnswer(string $value): string
    {
        $value = mb_strtolower(trim($value));

        return preg_replace('/\s+/', ' ', $value) ?? '';
    }

    public function normalizeNumericAnswer(string $value): string
    {
        $normalized = str_replace(',', '.', trim($value));
        if ($normalized === '' || ! is_numeric($normalized)) {
            return '';
        }

        return rtrim(rtrim(number_format((float) $normalized, 8, '.', ''), '0'), '.');
    }

    public function acceptsAnswer(QuestStep $step, string $answer, bool $numeric = false): bool
    {
        $normalize = $numeric ? $this->normalizeNumericAnswer(...) : $this->normalizeAnswer(...);
        $actual = $normalize($answer);
        if ($actual === '') return false;
        foreach (array_merge([(string) $step->correct_answer], $step->acceptable_answers ?? []) as $candidate) {
            if ($actual === $normalize((string) $candidate)) return true;
        }
        return false;
    }

    public function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earthRadius * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function isWithinStepLocation(QuestStep $step, float $latitude, float $longitude): bool
    {
        if (! $this->hasStepLocationConfig($step)) {
            return false;
        }

        $distance = $this->distanceMeters(
            $latitude,
            $longitude,
            (float) $step->location_lat,
            (float) $step->location_lng
        );

        return $distance <= (float) $step->location_radius_meters;
    }

    public function hasStepLocationConfig(QuestStep $step): bool
    {
        return isset($step->location_lat, $step->location_lng, $step->location_radius_meters)
            && (float) $step->location_radius_meters > 0;
    }

    public function applyReward(RouteProgress $progress, QuestStep $step): void
    {
        $stories = $progress->unlocked_story_entries ?? [];
        $suspects = $progress->unlocked_suspects ?? [];
        $letters = $progress->hidden_collected_letters ?? [];

        if (filled($step->reward_story_text)) {
            $stories[] = [
                'step_id' => $step->id,
                'title' => $step->title,
                'story' => $step->reward_story_text,
            ];
        }

        if (filled($step->reward_letter)) {
            $letters[] = [
                'step_id' => $step->id,
                'letter' => mb_substr((string) $step->reward_letter, 0, 1),
            ];
        }

        if (filled($step->reward_suspect_name)) {
            $suspectName = trim((string) $step->reward_suspect_name);
            $alreadyUnlocked = collect($suspects)
                ->contains(fn ($item) => ($item['name'] ?? '') === $suspectName);

            if (! $alreadyUnlocked) {
                $suspects[] = [
                    'step_id' => $step->id,
                    'name' => $suspectName,
                ];
            }
        }

        $progress->update([
            'unlocked_story_entries' => $stories,
            'unlocked_suspects' => $suspects,
            'hidden_collected_letters' => $letters,
        ]);
    }

    public function markLettersVisibleWhenFinished(GameRoute $group, RouteProgress $progress): void
    {
        $maxOrder = (int) $group->routeQuestSteps()->max('sort_order');
        $isFinished = $maxOrder > 0 && $progress->current_sort_order > $maxOrder;

        if ($isFinished && ! $progress->show_letters) {
            $letters = $progress->hidden_collected_letters ?? [];
            if (is_array($letters) && count($letters) > 1) {
                shuffle($letters);
            }

            $progress->update([
                'show_letters' => true,
                'hidden_collected_letters' => $letters,
            ]);
        }
    }

    public function startTimeout(RouteProgress $progress, int $stepSortOrder, int $seconds): void
    {
        $progress->update([
            'timeout_step_sort_order' => $stepSortOrder,
            'timeout_remaining_seconds' => max(0, $seconds),
            'timeout_last_tick_at' => now(),
            'timeout_paused_state' => false,
        ]);
    }

    public function tickTimeout(RouteProgress $progress): RouteProgress
    {
        if ((int) $progress->timeout_remaining_seconds <= 0) {
            if ((int) $progress->timeout_remaining_seconds !== 0 || ! $progress->timeout_paused_state) {
                $progress->update([
                    'timeout_remaining_seconds' => 0,
                    'timeout_paused_state' => true,
                    'timeout_last_tick_at' => now(),
                ]);
            }

            return $progress->refresh();
        }

        if ((bool) $progress->timeout_paused_state) {
            $progress->update([
                'timeout_paused_state' => false,
                'timeout_last_tick_at' => now(),
            ]);

            return $progress->refresh();
        }

        $lastTick = $progress->timeout_last_tick_at ?? now();
        $elapsed = max(1, now()->diffInSeconds($lastTick));

        if ($elapsed > 0) {
            $remaining = max(0, (int) $progress->timeout_remaining_seconds - $elapsed);
            $progress->update([
                'timeout_remaining_seconds' => $remaining,
                'timeout_last_tick_at' => now(),
                'timeout_paused_state' => false,
            ]);

            if ($remaining === 0) {
                $progress->update([
                    'timeout_step_sort_order' => null,
                    'timeout_paused_state' => true,
                ]);
            }
        }

        return $progress->refresh();
    }

    public function pauseTimeout(RouteProgress $progress): RouteProgress
    {
        $progress->update([
            'timeout_paused_state' => true,
            'timeout_last_tick_at' => now(),
        ]);

        return $progress->refresh();
    }
}
