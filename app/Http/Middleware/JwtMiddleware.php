<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if (!auth('api')->check()) {
                throw new TokenInvalidException();
            }
        } catch (Exception $err) {
            if ($err instanceof TokenInvalidException || $err instanceof TokenExpiredException) {
                abort(403, "Sem acesso");
            } else {
                abort(403, "Sem acesso");
            }
        }

        return $next($request);
    }
}
