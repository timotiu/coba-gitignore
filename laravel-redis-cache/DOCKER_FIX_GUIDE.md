# 🔧 Docker Build Fix Guide

## ❌ Error yang Terjadi

```
ERROR: unable to select packages:
  libonig-dev (no such package):
    required by: world[libonig-dev]
```

## ✅ Solusi yang Telah Diimplementasikan

### 1. **Package Name Fix**
Package `libonig-dev` tidak tersedia di Alpine Linux terbaru. Telah diganti dengan:

```dockerfile
# ❌ BEFORE (Error)
libonig-dev

# ✅ AFTER (Fixed)
oniguruma-dev
```

### 2. **Improved Dockerfile Structure**

#### **Before (Problematic)**
```dockerfile
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libonig-dev \          # ← Package not found
    libxml2-dev \
    # ... many packages at once
```

#### **After (Fixed)**
```dockerfile
# Install system dependencies in smaller groups
RUN apk add --no-cache \
    bash \
    curl \
    git \
    zip \
    unzip \
    mysql-client \
    redis \
    supervisor \
    nginx

# Install development dependencies separately
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    autoconf \
    g++ \
    make \
    oniguruma-dev \        # ← Correct package name
    libxml2-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    icu-dev
```

### 3. **Multi-stage Build for Production**

Dibuat `Dockerfile.prod` dengan multi-stage build untuk optimasi:

```dockerfile
# Stage 1: Builder
FROM php:8.4-fpm-alpine as builder
# Build dependencies and compile extensions

# Stage 2: Production
FROM php:8.4-fpm-alpine
# Only runtime dependencies
```

### 4. **Files yang Diperbaiki**

- ✅ `Dockerfile` - Fixed dengan package names yang benar
- ✅ `Dockerfile.prod` - Multi-stage build untuk production
- ✅ `build-and-test.sh` - Script automation untuk testing
- ✅ `DOCKER_FIX_GUIDE.md` - Dokumentasi fix ini

## 🚀 Cara Menggunakan Fix

### Option 1: Gunakan Script Automation
```bash
# Run automated build and test
./build-and-test.sh

# With cleanup
./build-and-test.sh --clean
```

### Option 2: Manual Build
```bash
# Build containers
docker-compose build

# Start services
docker-compose up -d

# Install dependencies
docker-compose exec app composer install

# Run migrations
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --class=ProductSeeder
```

### Option 3: Production Build
```bash
# Build production image
docker build -f Dockerfile.prod -t laravel-redis-cache:prod .

# Deploy production
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

## 🔍 Troubleshooting

### Issue 1: Package Not Found
```bash
# Check available packages
docker run --rm php:8.4-fpm-alpine apk search oniguruma

# Check Alpine version
docker run --rm php:8.4-fpm-alpine cat /etc/alpine-release
```

### Issue 2: PHP Extension Build Failed
```bash
# Check PHP configuration
docker-compose exec app php -m

# Check extension dependencies
docker-compose exec app php --ri redis
```

### Issue 3: Permission Issues
```bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data /var/www/storage
docker-compose exec app chmod -R 755 /var/www/storage
```

## 📋 Package Mapping Reference

| Old Package | New Package | Purpose |
|-------------|-------------|---------|
| `libonig-dev` | `oniguruma-dev` | PCRE regex library |
| `libmcrypt-dev` | `libsodium-dev` | Encryption (if needed) |
| `libfreetype6-dev` | `freetype-dev` | Font rendering |
| `libjpeg62-turbo-dev` | `libjpeg-turbo-dev` | JPEG support |

## 🎯 Optimization Tips

### 1. **Reduce Image Size**
```dockerfile
# Clean up build dependencies
RUN apk del .build-deps

# Use multi-stage builds
FROM php:8.4-fpm-alpine as builder
# ... build steps
FROM php:8.4-fpm-alpine as production
COPY --from=builder /app /app
```

### 2. **Layer Caching**
```dockerfile
# Copy composer files first (better caching)
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

# Then copy source code
COPY . .
RUN composer dump-autoload --optimize
```

### 3. **Security Hardening**
```dockerfile
# Run as non-root user
USER www-data

# Set read-only filesystem
RUN chmod -R 755 /var/www
```

## 🧪 Verification Steps

### 1. **Test Build**
```bash
# Build without cache
docker-compose build --no-cache

# Check services
docker-compose ps
```

### 2. **Test Application**
```bash
# Health check
curl http://localhost/health

# Cache test
curl http://localhost/demo/cache-test

# API test
curl http://localhost/api/products
```

### 3. **Performance Check**
```bash
# Check resource usage
docker stats

# Check logs
docker-compose logs app | grep -E "(ERROR|FATAL)"
```

## 🔧 Development Commands

```bash
# Quick setup
make dev-setup

# Build and test
./build-and-test.sh

# View logs
make logs-app

# Access shell
make shell

# Monitor performance
make monitor

# Health checks
make health
```

## 📚 Additional Resources

- [Alpine Linux Package Database](https://pkgs.alpinelinux.org/)
- [PHP Official Docker Images](https://hub.docker.com/_/php)
- [Docker Multi-stage Builds](https://docs.docker.com/develop/dev-best-practices/dockerfile_best-practices/)
- [Laravel Docker Best Practices](https://laravel.com/docs/deployment#docker)

---

**✅ Fix berhasil diimplementasikan! Docker build sekarang akan berjalan dengan sukses.**