<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->renderable(function (HttpException $e, Request $request) {
            $status = $e->getStatusCode();
            $messages = [
                403 => 'Anda tidak memiliki akses ke halaman ini.',
                404 => 'Halaman tidak ditemukan.',
                419 => 'Sesi telah berakhir. Silakan refresh halaman.',
                429 => 'Terlalu banyak request. Coba lagi nanti.',
                500 => 'Terjadi kesalahan pada server. Silakan coba lagi.',
                503 => 'Layanan sedang maintenance. Silakan coba lagi nanti.',
            ];

            if (!$request->expectsJson() && in_array($status, array_keys($messages))) {
                return response()->view('errors.generic', [
                    'code'    => $status,
                    'message' => $messages[$status],
                ], $status);
            }
        });
    })->create();
