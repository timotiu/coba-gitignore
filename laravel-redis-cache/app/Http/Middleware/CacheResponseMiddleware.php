<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheResponseMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, int $ttl = 300): Response
    {
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return $next($request);
        }

        // Don't cache if user is authenticated (unless specified)
        if ($request->user() && !$request->has('cache_auth')) {
            return $next($request);
        }

        // Generate cache key based on request
        $cacheKey = $this->generateCacheKey($request);

        // Try to get from cache
        $cachedResponse = Cache::get($cacheKey);
        
        if ($cachedResponse) {
            Log::info('Response served from cache', ['key' => $cacheKey]);
            
            return response($cachedResponse['content'])
                ->withHeaders($cachedResponse['headers'])
                ->header('X-Cache-Status', 'HIT')
                ->header('X-Cache-Key', $cacheKey);
        }

        // Process request
        $response = $next($request);

        // Only cache successful responses
        if ($response->getStatusCode() === 200) {
            $cacheData = [
                'content' => $response->getContent(),
                'headers' => $response->headers->all()
            ];

            Cache::put($cacheKey, $cacheData, $ttl);
            
            Log::info('Response cached', [
                'key' => $cacheKey,
                'ttl' => $ttl,
                'size' => strlen($response->getContent())
            ]);
        }

        return $response->header('X-Cache-Status', 'MISS')
                       ->header('X-Cache-Key', $cacheKey);
    }

    /**
     * Generate cache key for request
     */
    protected function generateCacheKey(Request $request): string
    {
        $key = 'http_cache:' . md5(
            $request->getUri() . 
            serialize($request->query()) . 
            serialize($request->headers->get('Accept', ''))
        );

        return $key;
    }
}