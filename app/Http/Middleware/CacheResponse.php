<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponse
{
    public function handle(Request $request, Closure $next, int $ttl = 600): Response
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Don't cache for authenticated users
        if ($request->user()) {
            return $next($request);
        }

        // Don't cache requests with query parameters
        if ($request->query->count() > 0) {
            return $next($request);
        }

        $cacheKey = 'page_cache:' . md5($request->fullUrl());

        $cachedResponse = Cache::get($cacheKey);

        if ($cachedResponse) {
            return response($cachedResponse['content'], $cachedResponse['status'])
                ->withHeaders(array_merge($cachedResponse['headers'], ['X-Cache' => 'HIT']));
        }

        $response = $next($request);

        // Only cache successful HTML responses
        if ($response->isSuccessful() && str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            Cache::put($cacheKey, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => array_intersect_key(
                    $response->headers->all(),
                    array_flip(['content-type', 'cache-control'])
                ),
            ], $ttl);

            $response->headers->set('X-Cache', 'MISS');
        }

        return $response;
    }
}
