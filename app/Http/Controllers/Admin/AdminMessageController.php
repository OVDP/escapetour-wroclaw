<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminMessage;
use App\Models\GameRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminMessageController extends Controller
{
    public function index(): View
    {
        return view('admin.messages.index', [
            'groups' => GameRoute::query()->orderBy('name')->get(['id', 'name']),
            'activeMessages' => AdminMessage::query()
                ->latest()
                ->limit(120)
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'audience' => ['required', 'in:all_active,group'],
            'game_route_id' => ['nullable', 'integer', 'exists:game_routes,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $groupId = $validated['audience'] === 'group' ? (int) ($validated['game_route_id'] ?? 0) : null;
        $group = $groupId ? GameRoute::find($groupId) : null;

        AdminMessage::create([
            'audience' => $validated['audience'],
            'title' => $validated['title'] ?? null,
            'message' => $validated['message'],
            'expires_at' => $validated['expires_at'] ?? null,
            'game_id' => $group?->game_id,
            'game_route_id' => $group?->id,
        ]);

        return redirect()->route('admin.messages.index')->with('success', 'Bericht verstuurd.');
    }

    public function destroy(AdminMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Bericht verwijderd.');
    }

    public function destroyAll(): RedirectResponse
    {
        AdminMessage::query()->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Alle berichten zijn verwijderd.');
    }
}
