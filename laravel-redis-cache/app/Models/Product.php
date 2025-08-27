<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get cached product by ID
     */
    public static function getCached($id)
    {
        $cacheKey = "product:{$id}";
        
        return Cache::remember($cacheKey, 3600, function () use ($id) {
            return self::find($id);
        });
    }

    /**
     * Get all active products with caching
     */
    public static function getActiveProductsCached()
    {
        $cacheKey = 'active_products';
        
        return Cache::remember($cacheKey, 1800, function () {
            return self::where('is_active', true)->orderBy('name')->get();
        });
    }

    /**
     * Get products by category with caching
     */
    public static function getProductsByCategoryCached($category)
    {
        $cacheKey = "products_category:{$category}";
        
        return Cache::remember($cacheKey, 1800, function () use ($category) {
            return self::where('category', $category)
                      ->where('is_active', true)
                      ->orderBy('name')
                      ->get();
        });
    }

    /**
     * Clear product cache when model is updated
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($product) {
            Cache::forget("product:{$product->id}");
            Cache::forget('active_products');
            if ($product->category) {
                Cache::forget("products_category:{$product->category}");
            }
        });

        static::deleted(function ($product) {
            Cache::forget("product:{$product->id}");
            Cache::forget('active_products');
            if ($product->category) {
                Cache::forget("products_category:{$product->category}");
            }
        });
    }
}
