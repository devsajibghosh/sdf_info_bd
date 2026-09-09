<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDonorApiKey
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {

        $providedKey = $request->header('X-API-KEY');

        $validKey = config('services.donor_api.key');

        if (
            empty($providedKey) ||
            empty($validKey) ||
            !hash_equals($validKey, $providedKey)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}