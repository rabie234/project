<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Disable CSRF for specific URIs.  You can use exact strings or
        // wildcards like 'api/*'. These are URIs **relative to your app root**
        // (no leading slash) – same rules as VerifyCsrfToken::$except.
        $middleware->validateCsrfTokens(
            except: [
                'register',
                'api/login',
                'addToCart',
                'EditQty',
                'checkout',
                'initial_checkout',
                'clearCart',
                'logout',
            ]
        );

        // If you have custom middleware you want to run on every request,
        // you can append it here, e.g.:
        // $middleware->append(\App\Http\Middleware\JwtMiddleware::class);

        // Register global dynamic tenant database resolver
        $middleware->append(\App\Http\Middleware\SetDatabaseConnection::class);

        // Register route middleware aliases
        $middleware->alias([
            'setSupportdb' => \App\Http\Middleware\getSupprtDataBase::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
