<?php

namespace App\Repositories;

use App\Models\Product;
use App\Services\CacheService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProductRepository
{
    protected $model;
    protected $cache;

    // Cache tags for grouped invalidation
    const CACHE_TAG = 'products';
    const CACHE_TAG_CATEGORIES = 'product_categories';
    const CACHE_TAG_ACTIVE = 'active_products';

    // Cache keys
    const CACHE_KEY_ALL_ACTIVE = 'all_active_products';
    const CACHE_KEY_CATEGORY_PREFIX = 'products_category_';
    const CACHE_KEY_PRODUCT_PREFIX = 'product_';
    const CACHE_KEY_CATEGORIES = 'product_categories_list';

    public function __construct(Product $model, CacheService $cache)
    {
        $this->model = $model;
        $this->cache = $cache;
    }

    /**
     * Get all active products with caching
     */
    public function getAllActive(): Collection
    {
        return $this->cache->rememberMedium(
            self::CACHE_KEY_ALL_ACTIVE,
            fn() => $this->model->where('is_active', true)->orderBy('name')->get(),
            [self::CACHE_TAG, self::CACHE_TAG_ACTIVE]
        );
    }

    /**
     * Get active products with pagination
     */
    public function getPaginatedActive(int $perPage = 15): LengthAwarePaginator
    {
        // Don't cache paginated results as they change frequently
        return $this->model->where('is_active', true)
                          ->orderBy('name')
                          ->paginate($perPage);
    }

    /**
     * Find product by ID with caching
     */
    public function findById(int $id): ?Product
    {
        return $this->cache->rememberLong(
            self::CACHE_KEY_PRODUCT_PREFIX . $id,
            fn() => $this->model->find($id),
            [self::CACHE_TAG]
        );
    }

    /**
     * Get products by category with caching
     */
    public function getByCategory(string $category): Collection
    {
        return $this->cache->rememberMedium(
            self::CACHE_KEY_CATEGORY_PREFIX . $category,
            fn() => $this->model->where('category', $category)
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->get(),
            [self::CACHE_TAG, self::CACHE_TAG_CATEGORIES]
        );
    }

    /**
     * Get all categories with caching
     */
    public function getCategories(): Collection
    {
        return $this->cache->rememberLong(
            self::CACHE_KEY_CATEGORIES,
            fn() => $this->model->select('category')
                               ->distinct()
                               ->whereNotNull('category')
                               ->where('is_active', true)
                               ->orderBy('category')
                               ->pluck('category'),
            [self::CACHE_TAG_CATEGORIES]
        );
    }

    /**
     * Search products with caching
     */
    public function search(string $query): Collection
    {
        $cacheKey = 'search_products_' . md5($query);
        
        return $this->cache->rememberShort(
            $cacheKey,
            fn() => $this->model->where('is_active', true)
                               ->where(function ($q) use ($query) {
                                   $q->where('name', 'like', "%{$query}%")
                                     ->orWhere('description', 'like', "%{$query}%");
                               })
                               ->orderBy('name')
                               ->get(),
            [self::CACHE_TAG]
        );
    }

    /**
     * Get products by price range with caching
     */
    public function getByPriceRange(float $minPrice, float $maxPrice): Collection
    {
        $cacheKey = 'products_price_range_' . $minPrice . '_' . $maxPrice;
        
        return $this->cache->rememberMedium(
            $cacheKey,
            fn() => $this->model->where('is_active', true)
                               ->whereBetween('price', [$minPrice, $maxPrice])
                               ->orderBy('price')
                               ->get(),
            [self::CACHE_TAG]
        );
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $threshold = 10): Collection
    {
        $cacheKey = 'low_stock_products_' . $threshold;
        
        return $this->cache->rememberShort(
            $cacheKey,
            fn() => $this->model->where('is_active', true)
                               ->where('stock', '<=', $threshold)
                               ->orderBy('stock')
                               ->get(),
            [self::CACHE_TAG]
        );
    }

    /**
     * Get featured products (high price, good stock)
     */
    public function getFeaturedProducts(int $limit = 10): Collection
    {
        return $this->cache->rememberMedium(
            'featured_products_' . $limit,
            fn() => $this->model->where('is_active', true)
                               ->where('stock', '>', 0)
                               ->orderBy('price', 'desc')
                               ->limit($limit)
                               ->get(),
            [self::CACHE_TAG]
        );
    }

    /**
     * Create new product and invalidate cache
     */
    public function create(array $data): Product
    {
        $product = $this->model->create($data);
        $this->invalidateCache($product);
        
        return $product;
    }

    /**
     * Update product and invalidate cache
     */
    public function update(int $id, array $data): bool
    {
        $product = $this->model->find($id);
        
        if (!$product) {
            return false;
        }

        $oldCategory = $product->category;
        $result = $product->update($data);
        
        if ($result) {
            $this->invalidateCache($product, $oldCategory);
        }
        
        return $result;
    }

    /**
     * Delete product and invalidate cache
     */
    public function delete(int $id): bool
    {
        $product = $this->model->find($id);
        
        if (!$product) {
            return false;
        }

        $result = $product->delete();
        
        if ($result) {
            $this->invalidateCache($product);
        }
        
        return $result;
    }

    /**
     * Soft delete product and invalidate cache
     */
    public function softDelete(int $id): bool
    {
        $product = $this->model->find($id);
        
        if (!$product) {
            return false;
        }

        $result = $product->update(['is_active' => false]);
        
        if ($result) {
            $this->invalidateCache($product);
        }
        
        return $result;
    }

    /**
     * Get cache statistics for products
     */
    public function getCacheStats(): array
    {
        $stats = [
            'cached_keys' => [],
            'cache_sizes' => [],
            'ttl_info' => []
        ];

        // Check main cache keys
        $mainKeys = [
            self::CACHE_KEY_ALL_ACTIVE,
            self::CACHE_KEY_CATEGORIES,
        ];

        foreach ($mainKeys as $key) {
            $stats['cached_keys'][$key] = $this->cache->has($key);
            $stats['ttl_info'][$key] = $this->cache->getTTL($key);
        }

        // Check category caches
        $categories = $this->getCategories();
        foreach ($categories as $category) {
            $key = self::CACHE_KEY_CATEGORY_PREFIX . $category;
            $stats['cached_keys'][$key] = $this->cache->has($key);
            $stats['ttl_info'][$key] = $this->cache->getTTL($key);
        }

        // Check individual product caches (sample)
        $products = $this->model->limit(5)->pluck('id');
        foreach ($products as $productId) {
            $key = self::CACHE_KEY_PRODUCT_PREFIX . $productId;
            $stats['cached_keys'][$key] = $this->cache->has($key);
            $stats['ttl_info'][$key] = $this->cache->getTTL($key);
        }

        return $stats;
    }

    /**
     * Clear all product-related cache
     */
    public function clearCache(): array
    {
        $clearedKeys = [];

        // Clear tagged cache
        $this->cache->flushTags([self::CACHE_TAG]);
        $this->cache->flushTags([self::CACHE_TAG_CATEGORIES]);
        $this->cache->flushTags([self::CACHE_TAG_ACTIVE]);
        
        $clearedKeys[] = 'Tagged cache cleared: ' . self::CACHE_TAG;
        $clearedKeys[] = 'Tagged cache cleared: ' . self::CACHE_TAG_CATEGORIES;
        $clearedKeys[] = 'Tagged cache cleared: ' . self::CACHE_TAG_ACTIVE;

        return $clearedKeys;
    }

    /**
     * Invalidate cache for specific product and related caches
     */
    protected function invalidateCache(Product $product, string $oldCategory = null): void
    {
        // Clear specific product cache
        $this->cache->forget(self::CACHE_KEY_PRODUCT_PREFIX . $product->id);

        // Clear general caches
        $this->cache->forget(self::CACHE_KEY_ALL_ACTIVE);
        $this->cache->forget(self::CACHE_KEY_CATEGORIES);

        // Clear category caches
        if ($product->category) {
            $this->cache->forget(self::CACHE_KEY_CATEGORY_PREFIX . $product->category);
        }

        if ($oldCategory && $oldCategory !== $product->category) {
            $this->cache->forget(self::CACHE_KEY_CATEGORY_PREFIX . $oldCategory);
        }

        // Clear search and other dynamic caches by tags
        $this->cache->flushTags([self::CACHE_TAG]);
    }

    /**
     * Warm up cache with frequently accessed data
     */
    public function warmUpCache(): array
    {
        $warmedKeys = [];

        // Warm up active products
        $this->getAllActive();
        $warmedKeys[] = self::CACHE_KEY_ALL_ACTIVE;

        // Warm up categories
        $this->getCategories();
        $warmedKeys[] = self::CACHE_KEY_CATEGORIES;

        // Warm up popular products (first 10)
        $popularProducts = $this->model->where('is_active', true)
                                      ->orderBy('id')
                                      ->limit(10)
                                      ->get();

        foreach ($popularProducts as $product) {
            $this->findById($product->id);
            $warmedKeys[] = self::CACHE_KEY_PRODUCT_PREFIX . $product->id;
        }

        // Warm up category caches
        $categories = $this->getCategories();
        foreach ($categories as $category) {
            $this->getByCategory($category);
            $warmedKeys[] = self::CACHE_KEY_CATEGORY_PREFIX . $category;
        }

        return $warmedKeys;
    }
}