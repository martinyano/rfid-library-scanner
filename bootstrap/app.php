<?php

use App\Http\Middleware\EnsureRfidApiKey;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'rfid.key' => EnsureRfidApiKey::class,
        ]);

        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('admin*')
            ? route('admin.login')
            : route('scan'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
