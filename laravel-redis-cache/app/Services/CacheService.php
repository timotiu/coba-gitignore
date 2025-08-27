<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Exception;

class CacheService
{
    protected $defaultTTL = 3600; // 1 hour
    protected $shortTTL = 300;    // 5 minutes
    protected $mediumTTL = 1800;  // 30 minutes
    protected $longTTL = 86400;   // 24 hours

    protected $prefix;
    protected $tags = [];

    public function __construct()
    {
        $this->prefix = config('cache.prefix', 'laravel_cache');
        $this->defaultTTL = config('cache.ttl.default', 3600);
        $this->shortTTL = config('cache.ttl.short', 300);
        $this->mediumTTL = config('cache.ttl.medium', 1800);
        $this->longTTL = config('cache.ttl.long', 86400);
    }

    /**
     * Set cache prefix for namespacing
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Set cache tags for grouped invalidation
     */
    public function tags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    /**
     * Remember cache with automatic serialization and error handling
     */
    public function remember(string $key, $callback, int $ttl = null, array $tags = []): mixed
    {
        $ttl = $ttl ?? $this->defaultTTL;
        $cacheKey = $this->buildKey($key);
        $tags = array_merge($this->tags, $tags);

        try {
            $cache = $tags ? Cache::tags($tags) : Cache::store();
            
            return $cache->remember($cacheKey, $ttl, function () use ($callback, $cacheKey) {
                $startTime = microtime(true);
                $result = is_callable($callback) ? $callback() : $callback;
                $endTime = microtime(true);
                
                $this->logCacheOperation('miss', $cacheKey, ($endTime - $startTime) * 1000);
                
                return $result;
            });
        } catch (Exception $e) {
            Log::error('Cache remember failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
                'tags' => $tags
            ]);
            
            // Fallback to direct execution
            return is_callable($callback) ? $callback() : $callback;
        }
    }

    /**
     * Put value in cache with tags support
     */
    public function put(string $key, $value, int $ttl = null, array $tags = []): bool
    {
        $ttl = $ttl ?? $this->defaultTTL;
        $cacheKey = $this->buildKey($key);
        $tags = array_merge($this->tags, $tags);

        try {
            $cache = $tags ? Cache::tags($tags) : Cache::store();
            $result = $cache->put($cacheKey, $value, $ttl);
            
            $this->logCacheOperation('put', $cacheKey);
            
            return $result;
        } catch (Exception $e) {
            Log::error('Cache put failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage(),
                'tags' => $tags
            ]);
            
            return false;
        }
    }

    /**
     * Get value from cache
     */
    public function get(string $key, $default = null)
    {
        $cacheKey = $this->buildKey($key);

        try {
            $value = Cache::get($cacheKey, $default);
            $this->logCacheOperation($value !== $default ? 'hit' : 'miss', $cacheKey);
            
            return $value;
        } catch (Exception $e) {
            Log::error('Cache get failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
            
            return $default;
        }
    }

    /**
     * Check if cache key exists
     */
    public function has(string $key): bool
    {
        $cacheKey = $this->buildKey($key);

        try {
            return Cache::has($cacheKey);
        } catch (Exception $e) {
            Log::error('Cache has failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Delete cache key
     */
    public function forget(string $key): bool
    {
        $cacheKey = $this->buildKey($key);

        try {
            $result = Cache::forget($cacheKey);
            $this->logCacheOperation('forget', $cacheKey);
            
            return $result;
        } catch (Exception $e) {
            Log::error('Cache forget failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Flush cache by tags
     */
    public function flushTags(array $tags): bool
    {
        try {
            Cache::tags($tags)->flush();
            $this->logCacheOperation('flush_tags', implode(',', $tags));
            
            return true;
        } catch (Exception $e) {
            Log::error('Cache flush tags failed', [
                'tags' => $tags,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get cache with short TTL
     */
    public function rememberShort(string $key, $callback, array $tags = []): mixed
    {
        return $this->remember($key, $callback, $this->shortTTL, $tags);
    }

    /**
     * Get cache with medium TTL
     */
    public function rememberMedium(string $key, $callback, array $tags = []): mixed
    {
        return $this->remember($key, $callback, $this->mediumTTL, $tags);
    }

    /**
     * Get cache with long TTL
     */
    public function rememberLong(string $key, $callback, array $tags = []): mixed
    {
        return $this->remember($key, $callback, $this->longTTL, $tags);
    }

    /**
     * Increment cache value
     */
    public function increment(string $key, int $value = 1): int|false
    {
        $cacheKey = $this->buildKey($key);

        try {
            $result = Cache::increment($cacheKey, $value);
            $this->logCacheOperation('increment', $cacheKey);
            
            return $result;
        } catch (Exception $e) {
            Log::error('Cache increment failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Decrement cache value
     */
    public function decrement(string $key, int $value = 1): int|false
    {
        $cacheKey = $this->buildKey($key);

        try {
            $result = Cache::decrement($cacheKey, $value);
            $this->logCacheOperation('decrement', $cacheKey);
            
            return $result;
        } catch (Exception $e) {
            Log::error('Cache decrement failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info();
            
            return [
                'redis_version' => $info['redis_version'] ?? 'unknown',
                'used_memory' => $info['used_memory_human'] ?? 'unknown',
                'connected_clients' => $info['connected_clients'] ?? 'unknown',
                'total_commands_processed' => $info['total_commands_processed'] ?? 'unknown',
                'keyspace_hits' => $info['keyspace_hits'] ?? 'unknown',
                'keyspace_misses' => $info['keyspace_misses'] ?? 'unknown',
                'hit_rate' => $this->calculateHitRate($info),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get cache stats', ['error' => $e->getMessage()]);
            
            return ['error' => 'Failed to retrieve cache statistics'];
        }
    }

    /**
     * Get cache keys by pattern
     */
    public function getKeys(string $pattern = '*'): array
    {
        try {
            $redis = Redis::connection();
            $keys = $redis->keys($this->buildKey($pattern));
            
            return array_map(function ($key) {
                return str_replace($this->prefix . ':', '', $key);
            }, $keys);
        } catch (Exception $e) {
            Log::error('Failed to get cache keys', [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Get TTL for cache key
     */
    public function getTTL(string $key): int
    {
        $cacheKey = $this->buildKey($key);

        try {
            $redis = Redis::connection();
            return $redis->ttl($cacheKey);
        } catch (Exception $e) {
            Log::error('Failed to get TTL', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
            
            return -1;
        }
    }

    /**
     * Build cache key with prefix
     */
    protected function buildKey(string $key): string
    {
        return $this->prefix . ':' . $key;
    }

    /**
     * Calculate cache hit rate
     */
    protected function calculateHitRate(array $info): string
    {
        $hits = (int)($info['keyspace_hits'] ?? 0);
        $misses = (int)($info['keyspace_misses'] ?? 0);
        $total = $hits + $misses;
        
        if ($total === 0) {
            return '0%';
        }
        
        $hitRate = ($hits / $total) * 100;
        return number_format($hitRate, 2) . '%';
    }

    /**
     * Log cache operations for monitoring
     */
    protected function logCacheOperation(string $operation, string $key, float $executionTime = null): void
    {
        if (config('cache.log_operations', false)) {
            Log::info('Cache operation', [
                'operation' => $operation,
                'key' => $key,
                'execution_time_ms' => $executionTime,
                'timestamp' => now()->toISOString()
            ]);
        }
    }

    /**
     * Reset tags for next operation
     */
    public function __destruct()
    {
        $this->tags = [];
    }
}