<?php

namespace App\Http\Controllers;

use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Exception;

class HealthController extends Controller
{
    protected $cache;

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Basic health check
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'service' => 'Laravel Redis Cache',
            'version' => app()->version()
        ]);
    }

    /**
     * Detailed health check
     */
    public function detailed(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'memory' => $this->checkMemory()
        ];

        $overallStatus = collect($checks)->every(fn($check) => $check['status'] === 'healthy') 
            ? 'healthy' 
            : 'unhealthy';

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => app()->environment(),
                'debug_mode' => config('app.debug'),
            ]
        ], $overallStatus === 'healthy' ? 200 : 503);
    }

    /**
     * Cache-specific health check
     */
    public function cache(): JsonResponse
    {
        $cacheCheck = $this->checkCache();
        $redisCheck = $this->checkRedis();
        
        $cacheStats = $this->cache->getStats();
        
        return response()->json([
            'status' => $cacheCheck['status'] === 'healthy' && $redisCheck['status'] === 'healthy' ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'cache_check' => $cacheCheck,
            'redis_check' => $redisCheck,
            'cache_stats' => $cacheStats,
            'configuration' => [
                'default_driver' => config('cache.default'),
                'redis_host' => config('database.redis.default.host'),
                'redis_port' => config('database.redis.default.port'),
                'cache_prefix' => config('cache.prefix'),
            ]
        ]);
    }

    /**
     * Performance metrics
     */
    public function metrics(): JsonResponse
    {
        $startTime = microtime(true);
        
        // Database performance test
        $dbStartTime = microtime(true);
        try {
            DB::select('SELECT 1');
            $dbTime = (microtime(true) - $dbStartTime) * 1000;
            $dbStatus = 'healthy';
        } catch (Exception $e) {
            $dbTime = null;
            $dbStatus = 'unhealthy';
        }

        // Redis performance test
        $redisStartTime = microtime(true);
        try {
            Redis::ping();
            $redisTime = (microtime(true) - $redisStartTime) * 1000;
            $redisStatus = 'healthy';
        } catch (Exception $e) {
            $redisTime = null;
            $redisStatus = 'unhealthy';
        }

        // Cache performance test
        $cacheStartTime = microtime(true);
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 10);
            Cache::get($testKey);
            Cache::forget($testKey);
            $cacheTime = (microtime(true) - $cacheStartTime) * 1000;
            $cacheStatus = 'healthy';
        } catch (Exception $e) {
            $cacheTime = null;
            $cacheStatus = 'unhealthy';
        }

        $totalTime = (microtime(true) - $startTime) * 1000;

        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'performance' => [
                'total_time_ms' => round($totalTime, 2),
                'database_time_ms' => $dbTime ? round($dbTime, 2) : null,
                'redis_time_ms' => $redisTime ? round($redisTime, 2) : null,
                'cache_time_ms' => $cacheTime ? round($cacheTime, 2) : null,
            ],
            'status_checks' => [
                'database' => $dbStatus,
                'redis' => $redisStatus,
                'cache' => $cacheStatus,
            ],
            'system_metrics' => [
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'memory_limit' => ini_get('memory_limit'),
            ]
        ]);
    }

    /**
     * Check database connectivity
     */
    protected function checkDatabase(): array
    {
        try {
            $startTime = microtime(true);
            DB::select('SELECT 1');
            $responseTime = (microtime(true) - $startTime) * 1000;

            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
                'connection' => config('database.default'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'connection' => config('database.default'),
            ];
        }
    }

    /**
     * Check Redis connectivity
     */
    protected function checkRedis(): array
    {
        try {
            $startTime = microtime(true);
            $redis = Redis::connection();
            $response = $redis->ping();
            $responseTime = (microtime(true) - $startTime) * 1000;

            $info = $redis->info();

            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
                'ping_response' => $response,
                'redis_version' => $info['redis_version'] ?? 'unknown',
                'used_memory' => $info['used_memory_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 'unknown',
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'host' => config('database.redis.default.host'),
                'port' => config('database.redis.default.port'),
            ];
        }
    }

    /**
     * Check cache functionality
     */
    protected function checkCache(): array
    {
        try {
            $startTime = microtime(true);
            $testKey = 'health_check_' . time();
            $testValue = 'health_test_value';

            // Test put
            Cache::put($testKey, $testValue, 10);
            
            // Test get
            $retrievedValue = Cache::get($testKey);
            
            // Test delete
            Cache::forget($testKey);
            
            $responseTime = (microtime(true) - $startTime) * 1000;

            if ($retrievedValue !== $testValue) {
                throw new Exception('Cache value mismatch');
            }

            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
                'driver' => config('cache.default'),
                'store' => config('cache.stores.' . config('cache.default') . '.driver'),
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'driver' => config('cache.default'),
            ];
        }
    }

    /**
     * Check storage accessibility
     */
    protected function checkStorage(): array
    {
        try {
            $testFile = storage_path('logs/health_check.tmp');
            $testContent = 'health_check_' . time();

            // Test write
            file_put_contents($testFile, $testContent);
            
            // Test read
            $content = file_get_contents($testFile);
            
            // Test delete
            unlink($testFile);

            if ($content !== $testContent) {
                throw new Exception('Storage content mismatch');
            }

            return [
                'status' => 'healthy',
                'writable' => is_writable(storage_path()),
                'storage_path' => storage_path(),
            ];
        } catch (Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'storage_path' => storage_path(),
                'writable' => is_writable(storage_path()),
            ];
        }
    }

    /**
     * Check memory usage
     */
    protected function checkMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        
        $usagePercentage = $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;
        
        return [
            'status' => $usagePercentage < 90 ? 'healthy' : 'warning',
            'usage_bytes' => $memoryUsage,
            'usage_human' => $this->formatBytes($memoryUsage),
            'peak_bytes' => $memoryPeak,
            'peak_human' => $this->formatBytes($memoryPeak),
            'limit_human' => ini_get('memory_limit'),
            'usage_percentage' => round($usagePercentage, 2),
        ];
    }

    /**
     * Parse memory limit string to bytes
     */
    protected function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($limit, -1));
        $value = (int)substr($limit, 0, -1);

        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return (int)$limit;
        }
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}