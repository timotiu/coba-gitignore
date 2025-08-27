<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of all products with caching
     */
    public function index()
    {
        $startTime = microtime(true);
        
        // Menggunakan cache untuk mengambil semua produk aktif
        $products = Product::getActiveProductsCached();
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // dalam milliseconds
        
        return response()->json([
            'success' => true,
            'data' => $products,
            'execution_time_ms' => round($executionTime, 2),
            'data_source' => 'Redis Cache',
            'cache_key' => 'active_products'
        ]);
    }

    /**
     * Display a specific product with caching
     */
    public function show($id)
    {
        $startTime = microtime(true);
        
        // Menggunakan cache untuk mengambil produk berdasarkan ID
        $product = Product::getCached($id);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $product,
            'execution_time_ms' => round($executionTime, 2),
            'data_source' => 'Redis Cache',
            'cache_key' => "product:{$id}"
        ]);
    }

    /**
     * Get products by category with caching
     */
    public function getByCategory($category)
    {
        $startTime = microtime(true);
        
        // Menggunakan cache untuk mengambil produk berdasarkan kategori
        $products = Product::getProductsByCategoryCached($category);
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        return response()->json([
            'success' => true,
            'data' => $products,
            'execution_time_ms' => round($executionTime, 2),
            'data_source' => 'Redis Cache',
            'cache_key' => "products_category:{$category}",
            'total_products' => $products->count()
        ]);
    }

    /**
     * Compare performance: cached vs non-cached data retrieval
     */
    public function performanceComparison($id)
    {
        // Test 1: Mengambil data tanpa cache (langsung dari database)
        $startTime1 = microtime(true);
        $productFromDB = Product::find($id);
        $endTime1 = microtime(true);
        $dbTime = ($endTime1 - $startTime1) * 1000;

        // Test 2: Mengambil data dengan cache
        $startTime2 = microtime(true);
        $productFromCache = Product::getCached($id);
        $endTime2 = microtime(true);
        $cacheTime = ($endTime2 - $startTime2) * 1000;

        // Test 3: Cek apakah data sudah ada di cache
        $cacheKey = "product:{$id}";
        $isCached = Cache::has($cacheKey);

        return response()->json([
            'success' => true,
            'performance_comparison' => [
                'database_query_time_ms' => round($dbTime, 4),
                'cache_query_time_ms' => round($cacheTime, 4),
                'performance_improvement' => $dbTime > 0 ? round((($dbTime - $cacheTime) / $dbTime) * 100, 2) . '%' : '0%',
                'is_cached' => $isCached
            ],
            'product' => $productFromCache
        ]);
    }

    /**
     * Create a new product and invalidate related caches
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string',
            'is_active' => 'boolean'
        ]);

        $product = Product::create($request->all());

        // Cache akan otomatis dibersihkan melalui model events

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product,
            'cache_cleared' => [
                'active_products',
                "products_category:{$product->category}"
            ]
        ], 201);
    }

    /**
     * Update a product and invalidate related caches
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'category' => 'sometimes|required|string',
            'is_active' => 'boolean'
        ]);

        $oldCategory = $product->category;
        $product->update($request->all());

        // Cache akan otomatis dibersihkan melalui model events

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
            'cache_cleared' => [
                "product:{$id}",
                'active_products',
                "products_category:{$oldCategory}",
                "products_category:{$product->category}"
            ]
        ]);
    }

    /**
     * Delete a product and invalidate related caches
     */
    public function destroy($id)
    {
        $product = Product::find($id);
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $category = $product->category;
        $product->delete();

        // Cache akan otomatis dibersihkan melalui model events

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
            'cache_cleared' => [
                "product:{$id}",
                'active_products',
                "products_category:{$category}"
            ]
        ]);
    }

    /**
     * Clear all product-related caches manually
     */
    public function clearCache()
    {
        $patterns = [
            'active_products',
            'product:*',
            'products_category:*'
        ];

        $clearedKeys = [];
        
        // Clear active products cache
        if (Cache::forget('active_products')) {
            $clearedKeys[] = 'active_products';
        }

        // Clear individual product caches
        $products = DB::table('products')->pluck('id');
        foreach ($products as $productId) {
            $key = "product:{$productId}";
            if (Cache::forget($key)) {
                $clearedKeys[] = $key;
            }
        }

        // Clear category caches
        $categories = DB::table('products')->distinct()->pluck('category');
        foreach ($categories as $category) {
            if ($category) {
                $key = "products_category:{$category}";
                if (Cache::forget($key)) {
                    $clearedKeys[] = $key;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product caches cleared successfully',
            'cleared_keys' => $clearedKeys,
            'total_cleared' => count($clearedKeys)
        ]);
    }

    /**
     * Get cache statistics
     */
    public function cacheStats()
    {
        $stats = [];

        // Check active products cache
        $stats['active_products'] = [
            'exists' => Cache::has('active_products'),
            'key' => 'active_products'
        ];

        // Check individual product caches
        $products = DB::table('products')->limit(5)->pluck('id');
        foreach ($products as $productId) {
            $key = "product:{$productId}";
            $stats['products'][] = [
                'exists' => Cache::has($key),
                'key' => $key,
                'product_id' => $productId
            ];
        }

        // Check category caches
        $categories = DB::table('products')->distinct()->limit(3)->pluck('category');
        foreach ($categories as $category) {
            if ($category) {
                $key = "products_category:{$category}";
                $stats['categories'][] = [
                    'exists' => Cache::has($key),
                    'key' => $key,
                    'category' => $category
                ];
            }
        }

        return response()->json([
            'success' => true,
            'cache_statistics' => $stats,
            'redis_info' => [
                'connection' => 'Connected to Redis',
                'driver' => config('cache.stores.redis.driver')
            ]
        ]);
    }
}
