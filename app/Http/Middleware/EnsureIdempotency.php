<?php

namespace App\Http\Middleware;

use App\Models\IdempotencyKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');

        if (!$key) {
            return response()->json([
                'message' => 'Idempotency-Key header is required.'
            ], 400);
        }

        $endpoint = $request->method() . '_' . $request->path();

        $existing = IdempotencyKey::where('key', $key)
            ->where('endpoint', $endpoint)
            ->first();

        if ($existing && $existing->response) {
            return response()->json(
                json_decode($existing->response, true),
                $existing->status_code 
            );
        }

        $response = $next($request);

        IdempotencyKey::updateOrCreate(
            [
                'key' => $key,
                'endpoint' => $endpoint,
            ],
            [
                'response' => $response->getContent(),
                'status_code' => $response->getStatusCode(),
            ]
        );

        return $response;
    }
}
