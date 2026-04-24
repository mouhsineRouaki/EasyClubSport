<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\ApplicationBuilder;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = new Application(dirname(__DIR__));

$environmentFile = $_SERVER['APP_ENV_FILE'] ?? $_ENV['APP_ENV_FILE'] ?? getenv('APP_ENV_FILE');

if (is_string($environmentFile) && $environmentFile !== '' && is_file($app->environmentPath().DIRECTORY_SEPARATOR.$environmentFile)) {
    $app->loadEnvironmentFrom($environmentFile);
}

return (new ApplicationBuilder($app))
    ->withKernels()
    ->withEvents()
    ->withCommands()
    ->withProviders()
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        [
            'prefix' => 'api',
            'middleware' => ['auth:sanctum'],
        ]
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
