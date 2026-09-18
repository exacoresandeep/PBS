<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.api_maintenance', false)) {
            return response()->json([
                'success' => false,
                'statusCode' => 503,
                'message' => 'API is currently under maintenance. Please try again later.',
            ], 503);
        }

        return $next($request);
    }
}