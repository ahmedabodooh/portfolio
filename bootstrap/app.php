<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability'   => CheckForAnyAbility::class,
        ]);

        // Never redirect to a login page for API requests — just 401.
        $middleware->redirectGuestsTo(function (Request $request) {
            return $request->is('api/*') ? null : '/admin';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });
    })
    ->booted(function (Application $app) {
        RateLimiter::for('api', fn (Request $r) => Limit::perMinute(120)->by($r->ip()));
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(10)->by($r->ip()));
        RateLimiter::for('contact', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));
    })
    ->create();
