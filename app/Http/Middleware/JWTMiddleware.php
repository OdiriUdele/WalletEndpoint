<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JWTMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(
                    [
                        'response' => [
                            'status'=>false,
                            'responseCode' => 422,
                            'responseDescription' => 'Token is Invalid.'
                    ]
                ], 422);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(
                    [ 
                        'response' => [
                            'status'=>false,
                            'responseCode' => 401,
                            'responseDescription' => 'Token is Expired.'
                    ]
                ], 401);
            }else{
                return response()->json(
                    [ 
                        'response' => [
                            'status'=>false,
                            'responseCode' => 403,
                            'responseDescription' => 'Authorization Token not found.'
                    ]
                ], 403);
            }
        }
        return $next($request);
    }
}
