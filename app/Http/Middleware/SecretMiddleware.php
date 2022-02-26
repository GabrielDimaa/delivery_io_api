<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecretMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $headers = $request->header();

        if (!isset($headers['secret']) || $headers['secret'][0] != env('JWT_SECRET')) {
            abort(403, "Sem acesso");
        }

        return $next($request);
    }
}
