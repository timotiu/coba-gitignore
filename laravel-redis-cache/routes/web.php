<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/redis-demo', function () {
    return view('redis-demo');
});

// API Routes untuk Product dengan Redis Caching
Route::prefix('api/products')->group(function () {
    // GET routes
    Route::get('/', [ProductController::class, 'index']); // Semua produk
    Route::get('/{id}', [ProductController::class, 'show']); // Produk specific
    Route::get('/category/{category}', [ProductController::class, 'getByCategory']); // Produk berdasarkan kategori
    Route::get('/{id}/performance', [ProductController::class, 'performanceComparison']); // Performance comparison
    
    // Management routes
    Route::post('/', [ProductController::class, 'store']); // Create produk baru
    Route::put('/{id}', [ProductController::class, 'update']); // Update produk
    Route::delete('/{id}', [ProductController::class, 'destroy']); // Delete produk
    
    // Cache management routes
    Route::post('/cache/clear', [ProductController::class, 'clearCache']); // Clear semua cache
    Route::get('/cache/stats', [ProductController::class, 'cacheStats']); // Statistics cache
});

// Routes untuk demo Redis cache
Route::prefix('demo')->group(function () {
    Route::get('/cache-test', function () {
        $key = 'demo_test_' . time();
        $value = 'Hello from Redis! ' . now();
        
        // Set cache untuk 10 menit
        Cache::put($key, $value, 600);
        
        // Get cache
        $cached = Cache::get($key);
        
        return response()->json([
            'message' => 'Redis Cache Demo',
            'cache_key' => $key,
            'cached_value' => $cached,
            'timestamp' => now(),
            'ttl_seconds' => 600
        ]);
    });
    
    Route::get('/cache-info', function () {
        return response()->json([
            'cache_driver' => config('cache.default'),
            'redis_host' => config('database.redis.default.host'),
            'redis_port' => config('database.redis.default.port'),
            'cache_prefix' => config('cache.prefix'),
            'test_connection' => Cache::put('connection_test', 'OK', 10) ? 'Success' : 'Failed'
        ]);
    });
});
