<?php

use App\Http\Middleware\AdminAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $proxies = array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', ''))));
        if ($proxies) {
            $middleware->trustProxies(at: array_values($proxies));
        }
        $middleware->web(append: [\App\Http\Middleware\IdentifyPlayerDevice::class]);
        $middleware->alias([
            'admin.auth' => AdminAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            $request = request();
            if ($response->getStatusCode() === 404
                && in_array($request->method(), ['GET', 'HEAD'], true)
                && ! $request->expectsJson()
                && $request->path() !== '/') {
                return redirect()->route('landing');
            }

            return $response;
        });
    })->create();
