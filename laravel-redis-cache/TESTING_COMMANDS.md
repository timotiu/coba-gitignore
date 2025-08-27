# Laravel Redis Cache - Testing Commands & URLs

## Prerequisites
Pastikan Redis server dan Laravel development server sudah berjalan:

```bash
# Start Redis (jika belum running)
redis-server --daemonize yes

# Start Laravel development server
php artisan serve --host=0.0.0.0 --port=8000
```

## Testing URLs

### Demo Page (Interactive UI)
```
http://localhost:8000/redis-demo
```
Halaman interaktif untuk testing semua fitur Redis cache.

### Demo API Endpoints

#### 1. Cache Connection Test
```bash
curl http://localhost:8000/demo/cache-test
```

#### 2. Cache Configuration Info
```bash
curl http://localhost:8000/demo/cache-info
```

### Product API Endpoints

#### 3. Get All Products (dengan caching)
```bash
curl http://localhost:8000/api/products
```
Response includes: execution_time_ms, data_source, cache_key

#### 4. Get Specific Product
```bash
curl http://localhost:8000/api/products/1
curl http://localhost:8000/api/products/2
```

#### 5. Get Products by Category
```bash
curl http://localhost:8000/api/products/category/Electronics
curl http://localhost:8000/api/products/category/Fashion
curl http://localhost:8000/api/products/category/Accessories
```

#### 6. Performance Comparison (DB vs Cache)
```bash
curl http://localhost:8000/api/products/1/performance
```
Shows database_query_time_ms vs cache_query_time_ms

#### 7. Cache Statistics
```bash
curl http://localhost:8000/api/products/cache/stats
```

#### 8. Clear All Cache
```bash
curl -X POST http://localhost:8000/api/products/cache/clear
```

## CRUD Operations (Test Cache Invalidation)

#### 9. Create New Product
```bash
curl -X POST http://localhost:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Product",
    "description": "Product untuk testing cache invalidation",
    "price": 100000,
    "stock": 50,
    "category": "Test",
    "is_active": true
  }'
```

#### 10. Update Product
```bash
curl -X PUT http://localhost:8000/api/products/1 \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Product Name",
    "price": 20000000
  }'
```

#### 11. Delete Product
```bash
curl -X DELETE http://localhost:8000/api/products/11
```

## Artisan Commands for Testing

#### 12. Test Redis Connection via Artisan
```bash
php artisan tinker
>>> Cache::put('test-manual', 'Hello Redis', 300)
>>> Cache::get('test-manual')
>>> Cache::forget('test-manual')
>>> exit
```

#### 13. Check Cache Content via Redis CLI
```bash
redis-cli
> KEYS *
> GET laravel_database_active_products
> TTL laravel_database_active_products
> FLUSHALL  # Clear all cache
> exit
```

## Expected Behavior Testing

### 1. Cache Miss to Cache Hit
```bash
# First request - Cache miss (slower)
time curl -s http://localhost:8000/api/products > /dev/null

# Second request - Cache hit (faster)
time curl -s http://localhost:8000/api/products > /dev/null
```

### 2. Cache Invalidation Test
```bash
# 1. Load products to cache
curl -s http://localhost:8000/api/products > /dev/null

# 2. Check cache exists
curl -s http://localhost:8000/api/products/cache/stats

# 3. Update a product (should invalidate cache)
curl -X PUT http://localhost:8000/api/products/1 \
  -H "Content-Type: application/json" \
  -d '{"name": "Updated Name"}'

# 4. Check cache again (should be invalidated)
curl -s http://localhost:8000/api/products/cache/stats
```

### 3. Performance Comparison
```bash
# Clear cache first
curl -X POST http://localhost:8000/api/products/cache/clear

# Test performance comparison
curl -s http://localhost:8000/api/products/1/performance | jq .performance_comparison
```

## JSON Response Examples

### Cache Info Response
```json
{
  "cache_driver": "redis",
  "redis_host": "127.0.0.1",
  "redis_port": "6379",
  "cache_prefix": null,
  "test_connection": "Success"
}
```

### Product API Response
```json
{
  "success": true,
  "data": [...],
  "execution_time_ms": 2.15,
  "data_source": "Redis Cache",
  "cache_key": "active_products"
}
```

### Performance Comparison Response
```json
{
  "success": true,
  "performance_comparison": {
    "database_query_time_ms": 15.245,
    "cache_query_time_ms": 1.657,
    "performance_improvement": "89.13%",
    "is_cached": true
  }
}
```

### Cache Statistics Response
```json
{
  "success": true,
  "cache_statistics": {
    "active_products": {"exists": true, "key": "active_products"},
    "products": [
      {"exists": true, "key": "product:1", "product_id": 1},
      {"exists": false, "key": "product:2", "product_id": 2}
    ],
    "categories": [
      {"exists": true, "key": "products_category:Electronics", "category": "Electronics"}
    ]
  }
}
```

## Key Features Demonstrated

1. **Cache-Aside Pattern**: Data dicari di cache dulu, jika tidak ada baru ke DB
2. **Automatic Cache Invalidation**: Cache otomatis dihapus saat data berubah
3. **TTL Strategy**: Cache dengan timeout sesuai kebutuhan
4. **Performance Monitoring**: Tracking execution time
5. **Cache Management**: Clear cache manual dan statistics
6. **Multiple Cache Keys**: Product individual, category-based, all products

## Redis Keys Used

- `laravel_database_active_products` - Cache semua produk aktif
- `laravel_database_product:{id}` - Cache produk individual
- `laravel_database_products_category:{category}` - Cache produk per kategori
- `laravel_database_demo_test_{timestamp}` - Demo cache test

## Troubleshooting

### If Redis connection fails:
```bash
sudo systemctl status redis-server
sudo systemctl start redis-server
redis-cli ping  # Should return PONG
```

### If Laravel cache not working:
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### Check logs:
```bash
tail -f storage/logs/laravel.log
tail -f server.log
```