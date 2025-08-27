# Docker Deployment Guide - Laravel Redis Cache

## 🐳 Docker Architecture Overview

Proyek ini menggunakan multi-container architecture dengan Docker Compose untuk production-ready deployment:

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Nginx Proxy   │    │  Laravel App    │    │  Queue Worker   │
│   (Port 80/443) │────▶│   (PHP-FPM)     │    │   (Background)  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │              ┌─────────────────┐              │
         │              │  Redis Cache    │              │
         │              │   (Port 6379)   │◀─────────────┘
         │              └─────────────────┘
         │                       │
         │              ┌─────────────────┐
         │              │  MySQL Database │
         └──────────────▶│   (Port 3306)   │
                        └─────────────────┘
```

## 📋 Services Overview

| Service | Purpose | Port | Health Check |
|---------|---------|------|--------------|
| **app** | Laravel Application (PHP-FPM) | 8000 | `/health` |
| **nginx** | Web Server & Load Balancer | 80, 443 | Built-in |
| **mysql** | Primary Database | 3306 | mysqladmin ping |
| **redis** | Cache & Session Store | 6379 | redis-cli ping |
| **queue** | Background Job Processing | - | Process monitor |
| **scheduler** | Cron Job Runner | - | Process monitor |
| **redis-commander** | Redis GUI (Development) | 8081 | HTTP check |
| **phpmyadmin** | MySQL GUI (Development) | 8080 | HTTP check |

## 🚀 Quick Start

### Prerequisites
- Docker 20.10+
- Docker Compose 2.0+
- 4GB+ RAM
- 10GB+ Disk Space

### 1. Clone and Setup
```bash
git clone <repository-url>
cd laravel-redis-cache

# Copy Docker environment file
cp .env.docker .env

# Build and start services
docker-compose up --build -d
```

### 2. Initialize Application
```bash
# Install dependencies
docker-compose exec app composer install --optimize-autoloader --no-dev

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations and seeders
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --class=ProductSeeder

# Cache optimization
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### 3. Verify Deployment
```bash
# Check service status
docker-compose ps

# Test application
curl http://localhost/health

# Test Redis cache
curl http://localhost/demo/cache-test

# Test API
curl http://localhost/api/products
```

## 🔧 Configuration

### Environment Variables

#### Application Settings
```env
APP_NAME="Laravel Redis Cache"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost
```

#### Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_redis_cache
DB_USERNAME=laravel
DB_PASSWORD=laravel_password
```

#### Redis Configuration
```env
CACHE_STORE=redis
REDIS_HOST=redis
REDIS_PASSWORD=redis_password
REDIS_PORT=6379

# Separate Redis databases for different purposes
REDIS_DB=0                    # Default
REDIS_CACHE_DB=1             # Cache
REDIS_SESSION_DB=2           # Sessions
REDIS_QUEUE_DB=3             # Queues
```

#### Cache Optimization
```env
CACHE_PREFIX=laravel_cache
CACHE_TTL_SHORT=300          # 5 minutes
CACHE_TTL_MEDIUM=1800        # 30 minutes
CACHE_TTL_LONG=3600          # 1 hour
CACHE_TTL_EXTENDED=86400     # 24 hours
```

### Redis Configuration Tuning

File: `docker/redis/redis.conf`

#### Memory Management
```conf
# Set appropriate memory limit
maxmemory 256mb

# Use LRU eviction policy
maxmemory-policy allkeys-lru

# Enable RDB and AOF persistence
save 900 1
save 300 10
save 60 10000
appendonly yes
```

#### Performance Optimization
```conf
# Optimize for Laravel workload
hash-max-ziplist-entries 512
hash-max-ziplist-value 64
list-max-ziplist-size -2
set-max-intset-entries 512
```

### MySQL Configuration Tuning

File: `docker/mysql/my.cnf`

#### Performance Settings
```conf
# InnoDB Configuration
innodb_buffer_pool_size=256M
innodb_log_file_size=64M
innodb_flush_method=O_DIRECT

# Query Cache
query_cache_type=1
query_cache_size=64M

# Connection Settings
max_connections=100
wait_timeout=600
```

## 📊 Monitoring & Health Checks

### Built-in Health Checks

#### Application Health
```bash
# Basic health check
curl http://localhost/health

# Detailed system check
curl http://localhost/health/detailed

# Cache-specific health
curl http://localhost/health/cache

# Performance metrics
curl http://localhost/health/metrics
```

#### Service Health Checks
```bash
# Check all services
docker-compose ps

# Check logs
docker-compose logs app
docker-compose logs redis
docker-compose logs mysql

# Check resource usage
docker stats
```

### Monitoring Tools

#### Redis Commander (Development)
- **URL**: http://localhost:8081
- **Purpose**: Visual Redis database browser
- **Credentials**: No authentication required

#### phpMyAdmin (Development)
- **URL**: http://localhost:8080
- **Username**: laravel
- **Password**: laravel_password

#### Application Metrics
```bash
# Cache statistics
curl http://localhost/api/products/cache/stats

# Performance comparison
curl http://localhost/api/products/1/performance

# Cache hit ratio
docker-compose exec redis redis-cli -a redis_password INFO stats
```

## 🔐 Security Best Practices

### 1. Environment Security
```bash
# Use strong passwords
MYSQL_ROOT_PASSWORD=<strong-random-password>
MYSQL_PASSWORD=<strong-random-password>
REDIS_PASSWORD=<strong-random-password>

# Disable debug mode in production
APP_DEBUG=false
```

### 2. Network Security
```bash
# Expose only necessary ports
# Remove development tools in production
docker-compose -f docker-compose.prod.yml up -d
```

### 3. SSL/TLS Configuration
```nginx
# nginx SSL configuration
server {
    listen 443 ssl http2;
    ssl_certificate /etc/ssl/certs/app.crt;
    ssl_certificate_key /etc/ssl/private/app.key;
    
    # Security headers
    add_header Strict-Transport-Security "max-age=31536000" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
}
```

## 🔧 Performance Optimization

### 1. OPcache Configuration
```ini
; docker/php/opcache.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.preload=/var/www/preload.php
```

### 2. PHP-FPM Tuning
```ini
; docker/php/local.ini
memory_limit=256M
max_execution_time=300
upload_max_filesize=40M
post_max_size=40M
```

### 3. Redis Optimization
```bash
# Monitor Redis performance
docker-compose exec redis redis-cli -a redis_password MONITOR

# Check slow queries
docker-compose exec redis redis-cli -a redis_password SLOWLOG GET 10

# Memory usage analysis
docker-compose exec redis redis-cli -a redis_password MEMORY USAGE <key>
```

### 4. MySQL Optimization
```sql
-- Check slow queries
SHOW GLOBAL STATUS LIKE 'Slow_queries';

-- Analyze query performance
EXPLAIN SELECT * FROM products WHERE category = 'Electronics';

-- Index optimization
SHOW INDEX FROM products;
```

## 🚀 Production Deployment

### 1. Production Docker Compose

Create `docker-compose.prod.yml`:
```yaml
version: '3.8'
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile.prod
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    # Remove development tools
    # Add resource limits
    deploy:
      resources:
        limits:
          memory: 512M
          cpus: '1.0'
        reservations:
          memory: 256M
          cpus: '0.5'

  redis:
    # Production Redis configuration
    command: redis-server /usr/local/etc/redis/redis.conf --requirepass ${REDIS_PASSWORD}
    deploy:
      resources:
        limits:
          memory: 256M
```

### 2. CI/CD Pipeline Example

`.github/workflows/deploy.yml`:
```yaml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Deploy to server
        run: |
          # Build and push Docker images
          docker build -t myapp:latest .
          docker push myapp:latest
          
          # Deploy with zero downtime
          docker-compose -f docker-compose.prod.yml up -d --force-recreate
          
          # Run post-deployment tasks
          docker-compose exec app php artisan migrate --force
          docker-compose exec app php artisan config:cache
```

### 3. Backup Strategy

#### Database Backup
```bash
# Create backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
docker-compose exec mysql mysqldump -u root -p${MYSQL_ROOT_PASSWORD} laravel_redis_cache > backup_${DATE}.sql
```

#### Redis Backup
```bash
# Redis data backup
docker-compose exec redis redis-cli -a redis_password SAVE
docker cp $(docker-compose ps -q redis):/data/dump.rdb ./redis_backup_$(date +%Y%m%d).rdb
```

## 🛠️ Troubleshooting

### Common Issues

#### 1. Redis Connection Failed
```bash
# Check Redis status
docker-compose exec redis redis-cli -a redis_password ping

# Check logs
docker-compose logs redis

# Fix permissions
docker-compose exec redis chown redis:redis /data
```

#### 2. Cache Not Working
```bash
# Clear all caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# Check Redis connectivity
docker-compose exec app php artisan tinker
>>> Cache::put('test', 'value', 60)
>>> Cache::get('test')
```

#### 3. Database Connection Issues
```bash
# Check MySQL status
docker-compose exec mysql mysqladmin -u root -p${MYSQL_ROOT_PASSWORD} ping

# Check connection from app
docker-compose exec app php artisan migrate:status
```

#### 4. Performance Issues
```bash
# Check resource usage
docker stats

# Monitor Redis memory
docker-compose exec redis redis-cli -a redis_password INFO memory

# Check slow logs
docker-compose logs app | grep "SLOW"
```

### 5. High Memory Usage
```bash
# Clear Redis cache
docker-compose exec redis redis-cli -a redis_password FLUSHALL

# Restart services
docker-compose restart app redis

# Check memory leaks
docker-compose exec app ps aux --sort=-%mem
```

## 📈 Scaling

### Horizontal Scaling
```yaml
# Scale specific services
services:
  app:
    deploy:
      replicas: 3
      
  queue:
    deploy:
      replicas: 2
```

### Load Balancing
```nginx
upstream app_servers {
    server app_1:9000;
    server app_2:9000;
    server app_3:9000;
}
```

### Redis Clustering
```yaml
services:
  redis-master:
    image: redis:7.2-alpine
    command: redis-server --port 6379 --requirepass ${REDIS_PASSWORD}
    
  redis-slave:
    image: redis:7.2-alpine
    command: redis-server --port 6379 --requirepass ${REDIS_PASSWORD} --slaveof redis-master 6379
```

## 📚 Additional Resources

- [Laravel Performance Best Practices](https://laravel.com/docs/deployment#optimization)
- [Redis Best Practices](https://redis.io/docs/manual/admin/)
- [Docker Security Guidelines](https://docs.docker.com/engine/security/)
- [MySQL Performance Tuning](https://dev.mysql.com/doc/refman/8.0/en/optimization.html)

---

**Happy Deploying! 🚀**