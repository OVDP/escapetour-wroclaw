<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): RedirectResponse
    {
        $game = $this->primaryGame();

        return redirect()->route('admin.games.edit', $game);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.games.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $game = $this->primaryGame();
        $game->update($this->validatedSettings($request));

        return redirect()->route('admin.games.edit', $game)->with('success', 'Spelinstellingen bijgewerkt.');
    }

    public function show(Game $game): View
    {
        return view('admin.games.show', compact('game'));
    }

    public function edit(Game $game): View
    {
        return view('admin.games.edit', compact('game'));
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $game->update($this->validatedSettings($request));

        return redirect()
            ->route('admin.games.edit', $game)
            ->with('success', 'Spelinstellingen bijgewerkt.');
    }

    public function destroy(Game $game): RedirectResponse
    {
        return redirect()
            ->route('admin.games.edit', $game)
            ->withErrors(['game' => 'Dit spel gebruikt een vaste single-case opzet en kan niet verwijderd worden.']);
    }

    private function validatedSettings(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'incident_location' => ['nullable', 'string', 'max:255'],
            'incident_at' => ['nullable', 'date'],
            'case_briefing' => ['nullable', 'string'],
            'possible_suspects_text' => ['nullable', 'string'],
            'story_intro' => ['nullable', 'string'],
            'story_mid' => ['nullable', 'string'],
            'story_final' => ['nullable', 'string'],
        ]);

        $validated['possible_suspects'] = $this->splitLines($validated['possible_suspects_text'] ?? null);
        $validated['possible_locations'] = [];

        unset($validated['possible_suspects_text']);

        return $validated;
    }

    private function splitLines(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $value);
        if (! is_array($lines)) {
            return [];
        }

        $lines = array_map(static fn ($line) => trim((string) $line), $lines);
        $lines = array_values(array_filter($lines, static fn ($line) => $line !== ''));

        return $lines;
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
