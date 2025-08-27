# Laravel Redis Cache - Best Practices Guide

## 🎯 Caching Strategy Overview

### Cache Hierarchy
```
1. Browser Cache (Client-side)
   ↓
2. CDN Cache (Edge locations)
   ↓ 
3. HTTP Cache (Nginx/Apache)
   ↓
4. Application Cache (Redis/Laravel)
   ↓
5. Database Query Cache (MySQL)
   ↓
6. Database (Final source)
```

## 🔑 Key Principles

### 1. Cache Invalidation Strategy

#### Cache-Aside Pattern (Lazy Loading)
```php
// ✅ GOOD: Cache-aside with TTL
public function getProduct($id)
{
    $cacheKey = "product:{$id}";
    
    return Cache::remember($cacheKey, 3600, function () use ($id) {
        return Product::find($id);
    });
}
```

#### Cache Invalidation on Updates
```php
// ✅ GOOD: Automatic cache invalidation
protected static function boot()
{
    parent::boot();
    
    static::saved(function ($product) {
        Cache::forget("product:{$product->id}");
        Cache::tags(['products'])->flush();
    });
}
```

### 2. TTL (Time To Live) Strategy

#### TTL Guidelines by Data Type
```php
class CacheTTL 
{
    const VERY_SHORT = 60;      // 1 minute  - Real-time data
    const SHORT = 300;          // 5 minutes - Frequently updated
    const MEDIUM = 1800;        // 30 minutes - Semi-static data
    const LONG = 3600;          // 1 hour - Static content
    const VERY_LONG = 86400;    // 24 hours - Configuration data
    const PERMANENT = 604800;   // 1 week - Rarely changing data
}

// Usage examples:
Cache::put('user_permissions', $permissions, CacheTTL::LONG);
Cache::put('product_categories', $categories, CacheTTL::VERY_LONG);
Cache::put('live_stock_count', $stock, CacheTTL::VERY_SHORT);
```

### 3. Cache Key Naming Convention

#### Structured Key Names
```php
// ✅ GOOD: Hierarchical and descriptive
$keys = [
    'user:123:profile',
    'product:456:details',
    'category:electronics:products',
    'stats:daily:2024-01-15',
    'search:products:electronics:page:1'
];

// ❌ BAD: Unclear and unstructured
$keys = [
    'u123',
    'prod456',
    'electronics',
    'stats',
    'search1'
];
```

#### Key Generation Helper
```php
class CacheKeyBuilder 
{
    public static function userProfile(int $userId): string 
    {
        return "user:{$userId}:profile";
    }
    
    public static function productsByCategory(string $category, int $page = 1): string 
    {
        return "products:category:{$category}:page:{$page}";
    }
    
    public static function searchResults(string $query, array $filters = []): string 
    {
        $filterString = empty($filters) ? 'all' : md5(serialize($filters));
        return "search:" . md5($query) . ":filters:{$filterString}";
    }
}
```

## 🏗️ Advanced Caching Patterns

### 1. Repository Pattern with Caching

```php
class ProductRepository 
{
    public function findById(int $id): ?Product
    {
        return $this->cache->remember(
            "product:{$id}",
            3600,
            fn() => Product::find($id)
        );
    }
    
    public function findByCategory(string $category): Collection
    {
        return $this->cache->remember(
            "products:category:{$category}",
            1800,
            fn() => Product::where('category', $category)->get()
        );
    }
    
    public function getPopular(int $limit = 10): Collection
    {
        return $this->cache->remember(
            "products:popular:{$limit}",
            900, // 15 minutes - more dynamic data
            fn() => Product::orderBy('views', 'desc')->limit($limit)->get()
        );
    }
}
```

### 2. Write-Through Cache Pattern

```php
class CacheWriteThroughService 
{
    public function updateProduct(int $id, array $data): Product
    {
        // 1. Update database
        $product = Product::findOrFail($id);
        $product->update($data);
        
        // 2. Update cache immediately
        $cacheKey = "product:{$id}";
        Cache::put($cacheKey, $product, 3600);
        
        // 3. Invalidate related caches
        $this->invalidateRelatedCaches($product);
        
        return $product;
    }
    
    private function invalidateRelatedCaches(Product $product): void
    {
        Cache::forget("products:category:{$product->category}");
        Cache::forget("products:popular:10");
        Cache::tags(['products', 'categories'])->flush();
    }
}
```

### 3. Cache Warming Strategy

```php
class CacheWarmupService 
{
    public function warmupProductCaches(): void
    {
        // Warm up popular products
        $popularProducts = Product::orderBy('views', 'desc')->limit(100)->get();
        foreach ($popularProducts as $product) {
            Cache::put("product:{$product->id}", $product, 3600);
        }
        
        // Warm up categories
        $categories = Product::distinct('category')->pluck('category');
        foreach ($categories as $category) {
            $products = Product::where('category', $category)->get();
            Cache::put("products:category:{$category}", $products, 1800);
        }
        
        // Warm up common searches
        $commonSearches = ['laptop', 'smartphone', 'keyboard'];
        foreach ($commonSearches as $term) {
            $results = Product::where('name', 'like', "%{$term}%")->get();
            Cache::put("search:results:" . md5($term), $results, 900);
        }
    }
}
```

## 🔧 Performance Optimization

### 1. Cache Serialization

#### Using igbinary for Better Performance
```php
// config/cache.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'cache',
    'serializer' => 'igbinary', // Faster than PHP's default
    'compression' => 'lz4',     // Compress large objects
],
```

#### Custom Serialization for Large Objects
```php
class OptimizedCacheService 
{
    public function putLargeObject(string $key, $data, int $ttl): void
    {
        // Compress large datasets
        $serialized = serialize($data);
        if (strlen($serialized) > 1024) { // 1KB threshold
            $compressed = gzcompress($serialized, 6);
            Cache::put($key . ':compressed', $compressed, $ttl);
        } else {
            Cache::put($key, $data, $ttl);
        }
    }
    
    public function getLargeObject(string $key)
    {
        // Try compressed version first
        $compressed = Cache::get($key . ':compressed');
        if ($compressed) {
            return unserialize(gzuncompress($compressed));
        }
        
        return Cache::get($key);
    }
}
```

### 2. Batch Operations

```php
class BatchCacheService 
{
    public function getMultiple(array $keys): array
    {
        // Use Redis pipeline for multiple operations
        return Cache::many($keys);
    }
    
    public function putMultiple(array $items, int $ttl): void
    {
        // Batch set operations
        foreach ($items as $key => $value) {
            Cache::put($key, $value, $ttl);
        }
    }
    
    public function warmupBatch(array $callbacks): void
    {
        // Execute multiple cache warming operations
        $pipeline = Redis::pipeline();
        
        foreach ($callbacks as $key => $callback) {
            $data = $callback();
            $pipeline->setex($key, 3600, serialize($data));
        }
        
        $pipeline->execute();
    }
}
```

### 3. Memory-Efficient Caching

```php
class MemoryEfficientCache 
{
    public function cachePagedResults(string $baseKey, $query, int $perPage = 20): void
    {
        // Cache paginated results efficiently
        $totalCount = $query->count();
        $totalPages = ceil($totalCount / $perPage);
        
        // Cache metadata
        Cache::put($baseKey . ':meta', [
            'total' => $totalCount,
            'pages' => $totalPages,
            'per_page' => $perPage
        ], 1800);
        
        // Cache first few pages only
        for ($page = 1; $page <= min(5, $totalPages); $page++) {
            $results = $query->forPage($page, $perPage)->get();
            Cache::put($baseKey . ":page:{$page}", $results, 1800);
        }
    }
    
    public function getPagedResults(string $baseKey, int $page): array
    {
        $meta = Cache::get($baseKey . ':meta');
        $results = Cache::get($baseKey . ":page:{$page}");
        
        if (!$results && $page <= $meta['pages']) {
            // Load on demand for higher page numbers
            $results = $this->loadPageFromDatabase($baseKey, $page, $meta['per_page']);
            Cache::put($baseKey . ":page:{$page}", $results, 1800);
        }
        
        return [
            'data' => $results,
            'meta' => $meta,
            'current_page' => $page
        ];
    }
}
```

## 🏷️ Cache Tagging Best Practices

### 1. Hierarchical Tagging

```php
class TaggedCacheService 
{
    public function cacheProductWithTags(Product $product): void
    {
        $tags = [
            'products',                    // All products
            'products:category:' . $product->category,  // Category-specific
            'products:user:' . $product->user_id,       // User-specific
            'products:status:' . ($product->is_active ? 'active' : 'inactive')
        ];
        
        Cache::tags($tags)->put(
            "product:{$product->id}",
            $product,
            3600
        );
    }
    
    public function invalidateProductsByCategory(string $category): void
    {
        Cache::tags(['products:category:' . $category])->flush();
    }
    
    public function invalidateUserProducts(int $userId): void
    {
        Cache::tags(['products:user:' . $userId])->flush();
    }
}
```

### 2. Smart Tag Management

```php
class SmartTagManager 
{
    private array $tagHierarchy = [
        'products' => [
            'products:categories',
            'products:users',
            'products:featured'
        ],
        'users' => [
            'users:profiles',
            'users:permissions',
            'users:preferences'
        ]
    ];
    
    public function flushWithHierarchy(string $rootTag): array
    {
        $flushedTags = [$rootTag];
        
        // Flush child tags
        if (isset($this->tagHierarchy[$rootTag])) {
            foreach ($this->tagHierarchy[$rootTag] as $childTag) {
                Cache::tags([$childTag])->flush();
                $flushedTags[] = $childTag;
            }
        }
        
        // Flush root tag
        Cache::tags([$rootTag])->flush();
        
        return $flushedTags;
    }
}
```

## 📊 Cache Monitoring & Analytics

### 1. Cache Hit Rate Monitoring

```php
class CacheMonitor 
{
    public function trackCacheOperation(string $operation, string $key, bool $hit = null): void
    {
        $stats = Cache::get('cache_stats', [
            'operations' => 0,
            'hits' => 0,
            'misses' => 0,
            'by_key_pattern' => []
        ]);
        
        $stats['operations']++;
        
        if ($hit !== null) {
            $stats[$hit ? 'hits' : 'misses']++;
        }
        
        // Track by key pattern
        $pattern = $this->getKeyPattern($key);
        $stats['by_key_pattern'][$pattern] = ($stats['by_key_pattern'][$pattern] ?? 0) + 1;
        
        Cache::put('cache_stats', $stats, 86400);
    }
    
    public function getCacheMetrics(): array
    {
        $stats = Cache::get('cache_stats', []);
        $redisInfo = Redis::info();
        
        return [
            'hit_rate' => $this->calculateHitRate($stats),
            'operations_count' => $stats['operations'] ?? 0,
            'popular_patterns' => $this->getPopularPatterns($stats),
            'redis_memory_usage' => $redisInfo['used_memory_human'] ?? 'unknown',
            'redis_keyspace_hits' => $redisInfo['keyspace_hits'] ?? 0,
            'redis_keyspace_misses' => $redisInfo['keyspace_misses'] ?? 0,
        ];
    }
    
    private function calculateHitRate(array $stats): float
    {
        $total = ($stats['hits'] ?? 0) + ($stats['misses'] ?? 0);
        return $total > 0 ? (($stats['hits'] ?? 0) / $total) * 100 : 0;
    }
}
```

### 2. Performance Profiling

```php
class CacheProfiler 
{
    private array $timings = [];
    
    public function profileCacheOperation(string $key, callable $operation)
    {
        $startTime = microtime(true);
        $result = $operation();
        $endTime = microtime(true);
        
        $this->timings[] = [
            'key' => $key,
            'duration_ms' => ($endTime - $startTime) * 1000,
            'timestamp' => time(),
            'result_size' => strlen(serialize($result))
        ];
        
        // Keep only last 1000 operations
        if (count($this->timings) > 1000) {
            array_shift($this->timings);
        }
        
        return $result;
    }
    
    public function getSlowOperations(float $thresholdMs = 100): array
    {
        return array_filter($this->timings, fn($timing) => $timing['duration_ms'] > $thresholdMs);
    }
    
    public function getAverageResponseTime(): float
    {
        if (empty($this->timings)) return 0;
        
        $total = array_sum(array_column($this->timings, 'duration_ms'));
        return $total / count($this->timings);
    }
}
```

## 🛡️ Cache Security Best Practices

### 1. Cache Key Sanitization

```php
class SecureCacheService 
{
    public function sanitizeKey(string $key): string
    {
        // Remove potentially dangerous characters
        $key = preg_replace('/[^a-zA-Z0-9:_\-.]/', '', $key);
        
        // Limit key length
        if (strlen($key) > 250) {
            $key = substr($key, 0, 200) . ':' . md5($key);
        }
        
        return $key;
    }
    
    public function secureGet(string $key, $default = null, int $userId = null)
    {
        $sanitizedKey = $this->sanitizeKey($key);
        
        // Add user context for sensitive data
        if ($userId) {
            $sanitizedKey = "user:{$userId}:" . $sanitizedKey;
        }
        
        return Cache::get($sanitizedKey, $default);
    }
}
```

### 2. Sensitive Data Handling

```php
class SensitiveDataCache 
{
    public function putSensitive(string $key, $data, int $ttl, bool $encrypt = true): void
    {
        if ($encrypt) {
            $data = encrypt($data);
        }
        
        // Use shorter TTL for sensitive data
        $safeTtl = min($ttl, 900); // Max 15 minutes
        
        Cache::put($key, $data, $safeTtl);
    }
    
    public function getSensitive(string $key, bool $encrypted = true)
    {
        $data = Cache::get($key);
        
        if ($data && $encrypted) {
            try {
                $data = decrypt($data);
            } catch (DecryptException $e) {
                // Handle decryption failure
                Cache::forget($key);
                return null;
            }
        }
        
        return $data;
    }
}
```

## 🚀 Production Optimization

### 1. Cache Warming Scheduler

```php
// In App\Console\Kernel.php
protected function schedule(Schedule $schedule)
{
    // Warm cache during low traffic hours
    $schedule->call(function () {
        app(CacheWarmupService::class)->warmupProductCaches();
    })->dailyAt('02:00');
    
    // Clear expired cache entries
    $schedule->call(function () {
        app(CacheCleanupService::class)->cleanExpiredEntries();
    })->hourly();
    
    // Generate cache reports
    $schedule->call(function () {
        app(CacheReportService::class)->generateDailyReport();
    })->dailyAt('06:00');
}
```

### 2. Graceful Cache Degradation

```php
class GracefulCacheService 
{
    public function getWithFallback(string $key, callable $fallback, int $ttl = 3600, int $gracePeriod = 300)
    {
        try {
            $cached = Cache::get($key);
            
            if ($cached !== null) {
                return $cached;
            }
            
            // Try to execute fallback
            $data = $fallback();
            
            // Cache the result
            Cache::put($key, $data, $ttl);
            
            return $data;
            
        } catch (Exception $e) {
            Log::error('Cache operation failed', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);
            
            // Try to get stale data
            $staleData = Cache::get($key . ':stale');
            if ($staleData) {
                // Extend stale data TTL
                Cache::put($key . ':stale', $staleData, $gracePeriod);
                return $staleData;
            }
            
            // Last resort: execute fallback without caching
            return $fallback();
        }
    }
}
```

## 📋 Cache Testing Strategies

### 1. Unit Testing

```php
class CacheServiceTest extends TestCase 
{
    public function test_product_caching()
    {
        $product = Product::factory()->create();
        
        // Test cache miss
        $this->assertNull(Cache::get("product:{$product->id}"));
        
        // Test cache population
        $cached = $this->cacheService->getProduct($product->id);
        $this->assertEquals($product->id, $cached->id);
        
        // Test cache hit
        $this->assertNotNull(Cache::get("product:{$product->id}"));
        
        // Test cache invalidation
        $product->update(['name' => 'Updated Name']);
        $this->assertNull(Cache::get("product:{$product->id}"));
    }
    
    public function test_cache_ttl()
    {
        $key = 'test_ttl_key';
        $value = 'test_value';
        
        Cache::put($key, $value, 1); // 1 second TTL
        
        $this->assertEquals($value, Cache::get($key));
        
        sleep(2);
        
        $this->assertNull(Cache::get($key));
    }
}
```

### 2. Integration Testing

```php
class CacheIntegrationTest extends TestCase 
{
    public function test_redis_connectivity()
    {
        $this->assertTrue(Redis::ping() === 'PONG');
    }
    
    public function test_cache_performance()
    {
        $startTime = microtime(true);
        
        // Perform 1000 cache operations
        for ($i = 0; $i < 1000; $i++) {
            Cache::put("test_key_{$i}", "test_value_{$i}", 60);
        }
        
        $duration = (microtime(true) - $startTime) * 1000;
        
        // Should complete within 1 second
        $this->assertLessThan(1000, $duration);
    }
}
```

---

## 📚 Summary Checklist

### ✅ Implementation Checklist

- [ ] **Cache Strategy Defined**: TTL, invalidation, warming
- [ ] **Key Naming Convention**: Consistent, hierarchical, descriptive  
- [ ] **Repository Pattern**: Abstracted caching logic
- [ ] **Tagged Caching**: Grouped invalidation strategy
- [ ] **Performance Monitoring**: Hit rates, response times
- [ ] **Security Measures**: Key sanitization, sensitive data handling
- [ ] **Graceful Degradation**: Fallback mechanisms
- [ ] **Testing Coverage**: Unit and integration tests
- [ ] **Documentation**: Clear usage guidelines
- [ ] **Monitoring & Alerting**: Health checks, performance metrics

### 🎯 Performance Targets

- **Cache Hit Rate**: > 80%
- **Cache Response Time**: < 5ms
- **Memory Usage**: < 256MB Redis
- **Cache Warming**: < 30 seconds
- **Invalidation Time**: < 100ms

---

**Happy Caching! 🚀**