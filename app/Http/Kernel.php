<?php

namespace App\Http;


use Symfony\Component\HttpKernel\HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        \App\Http\Middleware\Authenticate::class,
        \App\Http\Middleware\CorsMiddleware::class
    ];

}
