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

        // 1. Existing Aliases
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'check.syllabus' => \App\Http\Middleware\EnsureSyllabusSelected::class,
            'check.dynamic.role' => \App\Http\Middleware\CheckDynamicRole::class,
        ]);

        // 2. NEW: Register Referral Middleware globally for all Web Routes
        // Ye har page load par check karega ki URL me ?ref=CODE hai ya nahi
        $middleware->appendToGroup('web', \App\Http\Middleware\CheckReferral::class);

        // 3. CSRF Exclusions
        $middleware->validateCsrfTokens(except: [
            '/webhooks/razorpay',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
