<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
class IdentifyPlayerDevice {
    public function handle(Request $request, Closure $next) {
        $token = $request->cookie('tour_device') ?? $request->session()->get('tour_device');
        if (! is_string($token) || ! preg_match('/^[a-zA-Z0-9]{64}$/', $token)) {
            $token = Str::random(64);
        }
        $request->session()->put('tour_device', $token);
        $request->attributes->set('device_hash', hash('sha256', $token));
        Cookie::queue(cookie('tour_device', $token, 60 * 24 * 365 * 5, '/', null, $request->isSecure(), true, false, 'lax'));
        return $next($request);
    }
}
