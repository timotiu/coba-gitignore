#!/bin/bash

# Laravel Redis Cache - Build and Test Script

set -e

echo "🚀 Starting Laravel Redis Cache build and test..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    print_error "Docker is not running. Please start Docker first."
    exit 1
fi

print_status "Docker is running ✓"

# Stop any existing containers
print_status "Stopping existing containers..."
docker-compose down -v --remove-orphans || true

# Clean up old images (optional)
if [ "$1" == "--clean" ]; then
    print_warning "Cleaning up old Docker images..."
    docker system prune -f
    docker volume prune -f
fi

# Build containers
print_status "Building Docker containers..."
if docker-compose build --no-cache; then
    print_status "Docker build completed successfully ✓"
else
    print_error "Docker build failed ✗"
    exit 1
fi

# Start services
print_status "Starting services..."
if docker-compose up -d; then
    print_status "Services started successfully ✓"
else
    print_error "Failed to start services ✗"
    exit 1
fi

# Wait for services to be ready
print_status "Waiting for services to be ready..."
sleep 10

# Check service health
print_status "Checking service health..."

# Check if MySQL is ready
for i in {1..30}; do
    if docker-compose exec -T mysql mysqladmin ping -h localhost --silent; then
        print_status "MySQL is ready ✓"
        break
    fi
    if [ $i -eq 30 ]; then
        print_error "MySQL failed to start ✗"
        docker-compose logs mysql
        exit 1
    fi
    echo "Waiting for MySQL... ($i/30)"
    sleep 2
done

# Check if Redis is ready
for i in {1..30}; do
    if docker-compose exec -T redis redis-cli -a redis_password ping | grep -q PONG; then
        print_status "Redis is ready ✓"
        break
    fi
    if [ $i -eq 30 ]; then
        print_error "Redis failed to start ✗"
        docker-compose logs redis
        exit 1
    fi
    echo "Waiting for Redis... ($i/30)"
    sleep 2
done

# Install Laravel dependencies
print_status "Installing Laravel dependencies..."
if docker-compose exec app composer install --optimize-autoloader; then
    print_status "Dependencies installed successfully ✓"
else
    print_error "Failed to install dependencies ✗"
    exit 1
fi

# Generate application key
print_status "Generating application key..."
docker-compose exec app php artisan key:generate --force

# Run migrations
print_status "Running database migrations..."
if docker-compose exec app php artisan migrate --force; then
    print_status "Migrations completed successfully ✓"
else
    print_error "Migrations failed ✗"
    exit 1
fi

# Seed database
print_status "Seeding database..."
if docker-compose exec app php artisan db:seed --class=ProductSeeder --force; then
    print_status "Database seeded successfully ✓"
else
    print_warning "Database seeding failed, continuing..."
fi

# Cache configurations
print_status "Caching configurations..."
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache || true
docker-compose exec app php artisan view:cache || true

# Test application endpoints
print_status "Testing application endpoints..."

# Wait for app to be fully ready
sleep 5

# Test health endpoint
if curl -f -s http://localhost/health > /dev/null; then
    print_status "Health endpoint is working ✓"
else
    print_warning "Health endpoint test failed, checking app logs..."
    docker-compose logs app | tail -20
fi

# Test cache functionality
if curl -f -s http://localhost/demo/cache-test > /dev/null; then
    print_status "Cache test endpoint is working ✓"
else
    print_warning "Cache test failed, checking Redis connection..."
    docker-compose exec redis redis-cli -a redis_password ping || true
fi

# Test API endpoint
if curl -f -s http://localhost/api/products > /dev/null; then
    print_status "API endpoint is working ✓"
else
    print_warning "API endpoint test failed"
fi

# Show service status
print_status "Service status:"
docker-compose ps

# Show application URLs
echo ""
print_status "🎉 Setup completed! Application URLs:"
echo "  Main Application: http://localhost"
echo "  Redis Demo: http://localhost/redis-demo"
echo "  Health Check: http://localhost/health"
echo "  API Products: http://localhost/api/products"
echo "  Redis Commander: http://localhost:8081"
echo "  phpMyAdmin: http://localhost:8080"
echo ""

# Show useful commands
print_status "Useful commands:"
echo "  make logs          # View logs"
echo "  make shell         # Access app shell"
echo "  make monitor       # Monitor performance"
echo "  make health        # Check health"
echo "  make down          # Stop services"
echo ""

print_status "✅ Laravel Redis Cache is ready for development!"