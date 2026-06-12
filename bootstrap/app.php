<?php

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
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
        // Trust forwarding proxies (e.g. a demo tunnel / load balancer) so the
        // app honors X-Forwarded-Proto and generates correct https URLs.
        $middleware->trustProxies(at: '*');

        // Replace default ValidatePostSize with our 50MB version for vendor uploads
        $middleware->replace(
            \Illuminate\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\ValidatePostSize::class,
        );

        $middleware->alias([
            'vendor'    => \App\Http\Middleware\VendorMiddleware::class,
            'admin'     => \App\Http\Middleware\AdminMiddleware::class,
        ]);

        // Redirect already-authenticated users away from /login to the correct dashboard
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            $role = auth()->user()->role ?? '';
            if (in_array($role, ['admin', 'super_admin'])) {
                return route('admin.vendors.index');
            }
            return route('vendor.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
