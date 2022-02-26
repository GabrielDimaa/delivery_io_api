<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json("Token inválido");
            } else if ($e instanceof TokenExpiredException) {
                return response()->json("Token expirado");
            } else {
                return response()->json("Token de autorização não encontrado");
            }
        }

        return $next($request);
    }
}
