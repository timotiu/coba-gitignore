# Laravel Redis Cache - Docker Management Makefile

.PHONY: help build up down restart logs shell test deploy clean backup

# Default target
help: ## Show this help message
	@echo "Laravel Redis Cache - Docker Management"
	@echo "Usage: make [target]"
	@echo ""
	@echo "Available targets:"
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# Development Commands
build: ## Build Docker containers
	docker-compose build --no-cache

up: ## Start all services
	docker-compose up -d

down: ## Stop all services
	docker-compose down

restart: ## Restart all services
	docker-compose restart

logs: ## Show logs from all services
	docker-compose logs -f

logs-app: ## Show logs from Laravel app
	docker-compose logs -f app

logs-redis: ## Show logs from Redis
	docker-compose logs -f redis

logs-mysql: ## Show logs from MySQL
	docker-compose logs -f mysql

# Application Management
shell: ## Access Laravel app shell
	docker-compose exec app bash

shell-redis: ## Access Redis CLI
	docker-compose exec redis redis-cli -a redis_password

shell-mysql: ## Access MySQL CLI
	docker-compose exec mysql mysql -u laravel -p laravel_redis_cache

install: ## Install Laravel dependencies and setup
	docker-compose exec app composer install --optimize-autoloader
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate --force
	docker-compose exec app php artisan db:seed --class=ProductSeeder

setup: build up install ## Complete setup (build, start, install)

# Cache Management
cache-clear: ## Clear all application caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

cache-optimize: ## Optimize caches for production
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

cache-warmup: ## Warm up Redis cache
	docker-compose exec app php artisan tinker --execute="app('App\Repositories\ProductRepository')->warmUpCache();"

redis-flush: ## Flush all Redis data
	docker-compose exec redis redis-cli -a redis_password FLUSHALL

redis-info: ## Show Redis information
	docker-compose exec redis redis-cli -a redis_password INFO

redis-monitor: ## Monitor Redis commands
	docker-compose exec redis redis-cli -a redis_password MONITOR

# Database Management
db-fresh: ## Fresh database with seeds
	docker-compose exec app php artisan migrate:fresh --seed

db-backup: ## Backup MySQL database
	@mkdir -p backups
	docker-compose exec mysql mysqldump -u root -proot_password laravel_redis_cache > backups/db_backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "Database backup created in backups/ directory"

db-restore: ## Restore database from backup (usage: make db-restore FILE=backup.sql)
	@if [ -z "$(FILE)" ]; then echo "Usage: make db-restore FILE=backup.sql"; exit 1; fi
	docker-compose exec -T mysql mysql -u root -proot_password laravel_redis_cache < $(FILE)

# Testing
test: ## Run PHPUnit tests
	docker-compose exec app php artisan test

test-coverage: ## Run tests with coverage
	docker-compose exec app php artisan test --coverage

test-cache: ## Test cache functionality
	docker-compose exec app php artisan tinker --execute="Cache::put('test', 'OK', 60); echo Cache::get('test');"

# Health Checks
health: ## Check application health
	@echo "=== Application Health Check ==="
	@curl -s http://localhost/health | python3 -m json.tool || echo "Health check failed"

health-detailed: ## Detailed health check
	@echo "=== Detailed Health Check ==="
	@curl -s http://localhost/health/detailed | python3 -m json.tool || echo "Detailed health check failed"

health-cache: ## Cache-specific health check
	@echo "=== Cache Health Check ==="
	@curl -s http://localhost/health/cache | python3 -m json.tool || echo "Cache health check failed"

status: ## Show service status
	@echo "=== Docker Services Status ==="
	docker-compose ps
	@echo ""
	@echo "=== Application URLs ==="
	@echo "Application: http://localhost"
	@echo "Redis Demo: http://localhost/redis-demo"
	@echo "API Products: http://localhost/api/products"
	@echo "Health Check: http://localhost/health"
	@echo "Redis Commander: http://localhost:8081"
	@echo "phpMyAdmin: http://localhost:8080"

# Performance & Monitoring
stats: ## Show system statistics
	@echo "=== Docker Container Stats ==="
	docker stats --no-stream
	@echo ""
	@echo "=== Redis Stats ==="
	docker-compose exec redis redis-cli -a redis_password INFO stats | grep -E "keyspace_hits|keyspace_misses|used_memory_human"

monitor: ## Monitor key metrics
	@echo "Starting monitoring... (Press Ctrl+C to stop)"
	@while true; do \
		clear; \
		echo "=== $(shell date) ==="; \
		echo "=== Container Stats ==="; \
		docker stats --no-stream --format "table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}"; \
		echo ""; \
		echo "=== Redis Metrics ==="; \
		docker-compose exec redis redis-cli -a redis_password INFO stats | grep -E "keyspace_hits|keyspace_misses|used_memory_human|connected_clients"; \
		echo ""; \
		echo "=== Application Health ==="; \
		curl -s http://localhost/health/metrics | python3 -c "import sys, json; data=json.load(sys.stdin); print(f\"DB: {data['performance']['database_time_ms']}ms, Redis: {data['performance']['redis_time_ms']}ms, Cache: {data['performance']['cache_time_ms']}ms\")" 2>/dev/null || echo "Health check failed"; \
		sleep 5; \
	done

benchmark: ## Run performance benchmark
	@echo "=== Performance Benchmark ==="
	@echo "Testing cache performance..."
	@for i in {1..10}; do \
		curl -s -w "Response time: %{time_total}s\n" -o /dev/null http://localhost/api/products/1/performance; \
	done

# Production Commands
deploy-prod: ## Deploy to production
	docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d --build
	$(MAKE) cache-optimize

deploy-staging: ## Deploy to staging
	docker-compose -f docker-compose.yml -f docker-compose.staging.yml up -d --build

# Maintenance
clean: ## Clean up containers and volumes
	docker-compose down -v
	docker system prune -f

clean-all: ## Clean up everything including images
	docker-compose down -v --rmi all
	docker system prune -af

backup: ## Create full backup (database + Redis)
	@mkdir -p backups
	@echo "Creating database backup..."
	docker-compose exec mysql mysqldump -u root -proot_password laravel_redis_cache > backups/db_backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "Creating Redis backup..."
	docker-compose exec redis redis-cli -a redis_password SAVE
	docker cp $(shell docker-compose ps -q redis):/data/dump.rdb backups/redis_backup_$(shell date +%Y%m%d_%H%M%S).rdb
	@echo "Backup completed in backups/ directory"

restore: ## Restore from backup (usage: make restore DB=db.sql REDIS=redis.rdb)
	@if [ -n "$(DB)" ]; then \
		echo "Restoring database..."; \
		docker-compose exec -T mysql mysql -u root -proot_password laravel_redis_cache < $(DB); \
	fi
	@if [ -n "$(REDIS)" ]; then \
		echo "Restoring Redis data..."; \
		docker-compose stop redis; \
		docker cp $(REDIS) $(shell docker-compose ps -q redis):/data/dump.rdb; \
		docker-compose start redis; \
	fi

# Security
security-scan: ## Run security scan
	@echo "=== Security Scan ==="
	@echo "Checking for vulnerable dependencies..."
	docker-compose exec app composer audit
	@echo "Checking Docker security..."
	docker-compose exec app php artisan security:check

update: ## Update dependencies
	docker-compose exec app composer update
	docker-compose exec app php artisan migrate

# Development Helpers
watch-logs: ## Watch logs in real-time
	docker-compose logs -f app redis mysql

dev-setup: ## Setup for development
	$(MAKE) build
	$(MAKE) up
	$(MAKE) install
	$(MAKE) cache-warmup
	@echo ""
	@echo "=== Development Environment Ready! ==="
	@echo "Application: http://localhost"
	@echo "Redis Demo: http://localhost/redis-demo"
	@echo "Redis Commander: http://localhost:8081"
	@echo "phpMyAdmin: http://localhost:8080"

quick-test: ## Quick functionality test
	@echo "=== Quick Functionality Test ==="
	@echo "1. Testing basic health..."
	@curl -s http://localhost/health > /dev/null && echo "✓ Health check passed" || echo "✗ Health check failed"
	@echo "2. Testing cache..."
	@curl -s http://localhost/demo/cache-test > /dev/null && echo "✓ Cache test passed" || echo "✗ Cache test failed"
	@echo "3. Testing API..."
	@curl -s http://localhost/api/products > /dev/null && echo "✓ API test passed" || echo "✗ API test failed"
	@echo "4. Testing database..."
	@docker-compose exec mysql mysqladmin -u root -proot_password ping > /dev/null 2>&1 && echo "✓ Database test passed" || echo "✗ Database test failed"
	@echo "5. Testing Redis..."
	@docker-compose exec redis redis-cli -a redis_password ping > /dev/null 2>&1 && echo "✓ Redis test passed" || echo "✗ Redis test failed"

# Environment specific
env-dev: ## Switch to development environment
	cp .env.example .env

env-prod: ## Switch to production environment
	cp .env.docker .env

# Documentation
docs: ## Generate/update documentation
	@echo "Documentation files:"
	@echo "- README.md - Project overview"
	@echo "- DOCKER_DEPLOYMENT_GUIDE.md - Docker deployment guide"
	@echo "- CACHE_BEST_PRACTICES.md - Caching best practices"
	@echo "- REDIS_CACHE_GUIDE.md - Redis implementation guide"
	@echo "- TESTING_COMMANDS.md - Testing commands reference"

# Show current configuration
config: ## Show current configuration
	@echo "=== Current Configuration ==="
	@echo "Docker Compose Services:"
	docker-compose config --services
	@echo ""
	@echo "Environment:"
	@grep -E "^(APP_|DB_|REDIS_|CACHE_)" .env 2>/dev/null || echo "No .env file found"