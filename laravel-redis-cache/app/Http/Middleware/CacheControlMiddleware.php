<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CacheControlMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $cacheControl = 'public', int $maxAge = 3600): Response
    {
        $response = $next($request);

        // Only add cache headers to successful responses
        if ($response->getStatusCode() === 200) {
            $response->header('Cache-Control', "{$cacheControl}, max-age={$maxAge}")
                    ->header('Expires', gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT')
                    ->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
                    ->header('ETag', '"' . md5($response->getContent()) . '"');
        }

        return $response;
    }
}