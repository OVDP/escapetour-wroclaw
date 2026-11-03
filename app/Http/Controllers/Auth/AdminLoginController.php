<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (session('admin_authenticated')) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'passphrase' => ['required', 'string'],
        ]);

        $expectedPassphrase = (string) env('ADMIN_PASSPHRASE', '');

        if ($expectedPassphrase === '' || ! hash_equals($expectedPassphrase, $validated['passphrase'])) {
            return back()
                ->withErrors(['passphrase' => 'Onjuiste admin passphrase.']);
        }

        $request->session()->regenerate();
        $request->session()->put('admin_authenticated', true);

        return redirect()->route('admin.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Je bent uitgelogd.');
    }
}
