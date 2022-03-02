<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        return $response;

         $headers = [
             'Access-Control-Allow-Origin' => '*',
             'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE, PATCH',
             'Access-Control-Allow-Credentials' => 'true',
             'Access-Control-Max-Age' => '86400',
             'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With'
         ];

         if ($request->isMethod('OPTIONS')) {
             return response()->json('{"method":"OPTIONS"}', 200, $headers);
         }

         foreach ($headers as $key => $value) {
             if ($response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
                 $response->headers->set($key, $value);
                 continue;
             }

             $response->header($key, $value);
         }

         return $response;
    }
}
