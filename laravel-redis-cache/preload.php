<?php

/**
 * OPcache Preload Script for Laravel Redis Cache
 * 
 * This script preloads frequently used classes to improve performance
 * when OPcache is enabled.
 */

if (!function_exists('opcache_compile_file')) {
    return;
}

$appDir = __DIR__;

// Laravel Core Classes
$coreClasses = [
    // Application Bootstrap
    $appDir . '/vendor/laravel/framework/src/Illuminate/Foundation/Application.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Container/Container.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Support/ServiceProvider.php',
    
    // HTTP Kernel
    $appDir . '/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Routing/Router.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Http/Request.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Http/Response.php',
    
    // Cache System
    $appDir . '/vendor/laravel/framework/src/Illuminate/Cache/CacheManager.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Cache/Repository.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Cache/RedisStore.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Redis/RedisManager.php',
    
    // Database
    $appDir . '/vendor/laravel/framework/src/Illuminate/Database/DatabaseManager.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Database/Connection.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php',
    
    // Logging
    $appDir . '/vendor/laravel/framework/src/Illuminate/Log/LogManager.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/Log/Logger.php',
    
    // Config
    $appDir . '/vendor/laravel/framework/src/Illuminate/Config/Repository.php',
    
    // Events
    $appDir . '/vendor/laravel/framework/src/Illuminate/Events/Dispatcher.php',
    
    // View
    $appDir . '/vendor/laravel/framework/src/Illuminate/View/Factory.php',
    $appDir . '/vendor/laravel/framework/src/Illuminate/View/View.php',
];

// Application Classes
$appClasses = [
    // Models
    $appDir . '/app/Models/Product.php',
    $appDir . '/app/Models/User.php',
    
    // Controllers
    $appDir . '/app/Http/Controllers/Controller.php',
    $appDir . '/app/Http/Controllers/ProductController.php',
    $appDir . '/app/Http/Controllers/HealthController.php',
    
    // Services
    $appDir . '/app/Services/CacheService.php',
    
    // Repositories
    $appDir . '/app/Repositories/ProductRepository.php',
    
    // Middleware
    $appDir . '/app/Http/Middleware/CacheResponseMiddleware.php',
    $appDir . '/app/Http/Middleware/CacheControlMiddleware.php',
    
    // Providers
    $appDir . '/app/Providers/AppServiceProvider.php',
    $appDir . '/app/Providers/RouteServiceProvider.php',
];

// Config files
$configFiles = [
    $appDir . '/config/app.php',
    $appDir . '/config/cache.php',
    $appDir . '/config/database.php',
    $appDir . '/config/logging.php',
    $appDir . '/config/services.php',
];

$totalFiles = 0;
$compiledFiles = 0;
$errors = [];

// Compile core classes
foreach ($coreClasses as $file) {
    if (file_exists($file)) {
        $totalFiles++;
        try {
            if (opcache_compile_file($file)) {
                $compiledFiles++;
            }
        } catch (Throwable $e) {
            $errors[] = "Error compiling {$file}: " . $e->getMessage();
        }
    }
}

// Compile application classes
foreach ($appClasses as $file) {
    if (file_exists($file)) {
        $totalFiles++;
        try {
            if (opcache_compile_file($file)) {
                $compiledFiles++;
            }
        } catch (Throwable $e) {
            $errors[] = "Error compiling {$file}: " . $e->getMessage();
        }
    }
}

// Compile config files
foreach ($configFiles as $file) {
    if (file_exists($file)) {
        $totalFiles++;
        try {
            if (opcache_compile_file($file)) {
                $compiledFiles++;
            }
        } catch (Throwable $e) {
            $errors[] = "Error compiling {$file}: " . $e->getMessage();
        }
    }
}

// Auto-discover and compile additional classes
$composerLoader = $appDir . '/vendor/autoload.php';
if (file_exists($composerLoader)) {
    require_once $composerLoader;
    
    // Preload commonly used vendor classes
    $vendorClasses = [
        // Carbon (date handling)
        Carbon\Carbon::class,
        Carbon\CarbonImmutable::class,
        
        // Collections
        Illuminate\Support\Collection::class,
        
        // Facades
        Illuminate\Support\Facades\Cache::class,
        Illuminate\Support\Facades\DB::class,
        Illuminate\Support\Facades\Log::class,
        Illuminate\Support\Facades\Redis::class,
        Illuminate\Support\Facades\Config::class,
        
        // HTTP
        Symfony\Component\HttpFoundation\Request::class,
        Symfony\Component\HttpFoundation\Response::class,
    ];
    
    foreach ($vendorClasses as $className) {
        if (class_exists($className)) {
            $totalFiles++;
            $reflection = new ReflectionClass($className);
            $filename = $reflection->getFileName();
            
            if ($filename && file_exists($filename)) {
                try {
                    if (opcache_compile_file($filename)) {
                        $compiledFiles++;
                    }
                } catch (Throwable $e) {
                    $errors[] = "Error compiling class {$className}: " . $e->getMessage();
                }
            }
        }
    }
}

// Log preload results
$logFile = $appDir . '/storage/logs/opcache_preload.log';
$logContent = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_files' => $totalFiles,
    'compiled_files' => $compiledFiles,
    'success_rate' => $totalFiles > 0 ? round(($compiledFiles / $totalFiles) * 100, 2) . '%' : '0%',
    'errors' => $errors
];

file_put_contents($logFile, json_encode($logContent, JSON_PRETTY_PRINT) . "\n", FILE_APPEND | LOCK_EX);

// Output summary for debugging
if (php_sapi_name() === 'cli') {
    echo "OPcache Preload Summary:\n";
    echo "Total files: {$totalFiles}\n";
    echo "Compiled files: {$compiledFiles}\n";
    echo "Success rate: " . ($totalFiles > 0 ? round(($compiledFiles / $totalFiles) * 100, 2) . '%' : '0%') . "\n";
    
    if (!empty($errors)) {
        echo "Errors:\n";
        foreach ($errors as $error) {
            echo "  - {$error}\n";
        }
    }
}