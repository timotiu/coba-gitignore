# 🔄 Alternative Setup Guide (Without Docker)

Jika Anda mengalami masalah dengan Docker atau ingin menggunakan setup lokal, berikut adalah panduan alternative.

## 🛠️ Local Development Setup

### Prerequisites
- PHP 8.4+
- Composer
- Redis Server
- MySQL/MariaDB
- Nginx/Apache (optional)

### 1. Install Dependencies

#### Ubuntu/Debian
```bash
# Update package list
sudo apt update

# Install PHP and extensions
sudo apt install php8.4 php8.4-cli php8.4-fpm php8.4-mysql php8.4-redis \
    php8.4-xml php8.4-curl php8.4-mbstring php8.4-zip php8.4-bcmath \
    php8.4-gd php8.4-intl php8.4-opcache

# Install Redis
sudo apt install redis-server

# Install MySQL
sudo apt install mysql-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

#### macOS (with Homebrew)
```bash
# Install PHP
brew install php@8.4

# Install Redis
brew install redis

# Install MySQL
brew install mysql

# Install Composer
brew install composer

# Start services
brew services start redis
brew services start mysql
```

#### Windows (with Chocolatey)
```powershell
# Install PHP
choco install php

# Install Redis
choco install redis-64

# Install MySQL
choco install mysql

# Install Composer
choco install composer
```

### 2. Setup Application

```bash
# Clone repository
git clone <repository-url>
cd laravel-redis-cache

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Configure Environment

Edit `.env` file:
```env
APP_NAME="Laravel Redis Cache"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_redis_cache
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Cache Configuration
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4. Setup Database

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE laravel_redis_cache;"

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed --class=ProductSeeder
```

### 5. Start Services

```bash
# Start Redis (if not running)
redis-server --daemonize yes

# Start Laravel development server
php artisan serve --host=0.0.0.0 --port=8000
```

### 6. Verify Setup

```bash
# Test Redis connection
redis-cli ping

# Test Laravel application
curl http://localhost:8000/health

# Test cache functionality
curl http://localhost:8000/demo/cache-test
```

## 🐳 Hybrid Setup (Partial Docker)

Jika Anda ingin menggunakan beberapa service dengan Docker:

### Option 1: Only Database Services
```yaml
# docker-compose.services.yml
version: '3.8'
services:
  mysql:
    image: mysql:8.0
    ports:
      - "3306:3306"
    environment:
      MYSQL_DATABASE: laravel_redis_cache
      MYSQL_USER: laravel
      MYSQL_PASSWORD: laravel_password
      MYSQL_ROOT_PASSWORD: root_password

  redis:
    image: redis:7.2-alpine
    ports:
      - "6379:6379"
    command: redis-server --requirepass redis_password
```

```bash
# Start only database services
docker-compose -f docker-compose.services.yml up -d

# Use local PHP for application
php artisan serve
```

### Option 2: Only Redis
```bash
# Start Redis with Docker
docker run -d --name redis -p 6379:6379 redis:alpine redis-server --requirepass redis_password

# Use local MySQL and PHP
mysql -u root -p -e "CREATE DATABASE laravel_redis_cache;"
php artisan serve
```

## 🔧 Development Tools Setup

### 1. Configure PHP for Development

Create `php.ini` or edit existing:
```ini
; Development settings
display_errors = On
error_reporting = E_ALL
memory_limit = 256M
max_execution_time = 300

; OPcache settings
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 4000

; Redis session
session.save_handler = redis
session.save_path = "tcp://127.0.0.1:6379"
```

### 2. Setup Nginx (Optional)

Create `/etc/nginx/sites-available/laravel-redis-cache`:
```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/laravel-redis-cache/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/laravel-redis-cache /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 🧪 Testing Local Setup

### 1. Test All Components
```bash
# Test PHP
php -v

# Test Composer
composer --version

# Test MySQL connection
mysql -u root -p -e "SELECT 1"

# Test Redis connection
redis-cli ping

# Test Laravel
php artisan --version
```

### 2. Run Application Tests
```bash
# Clear caches
php artisan cache:clear
php artisan config:clear

# Test cache functionality
php artisan tinker
>>> Cache::put('test', 'value', 60)
>>> Cache::get('test')
>>> exit

# Test API endpoints
curl http://localhost:8000/api/products
curl http://localhost:8000/health
```

### 3. Performance Testing
```bash
# Install Apache Bench (optional)
sudo apt install apache2-utils

# Run performance test
ab -n 100 -c 10 http://localhost:8000/api/products
```

## 🔍 Troubleshooting Local Setup

### Issue 1: PHP Extensions Missing
```bash
# Check installed extensions
php -m | grep -E "(redis|mysql|mbstring)"

# Install missing extensions (Ubuntu)
sudo apt install php8.4-redis php8.4-mysql php8.4-mbstring
```

### Issue 2: Redis Connection Failed
```bash
# Check if Redis is running
ps aux | grep redis

# Start Redis
redis-server /etc/redis/redis.conf

# Test connection
redis-cli ping
```

### Issue 3: MySQL Connection Issues
```bash
# Check MySQL status
sudo systemctl status mysql

# Reset MySQL password
sudo mysql_secure_installation

# Test connection
mysql -u root -p -e "SHOW DATABASES;"
```

### Issue 4: Permission Issues
```bash
# Fix Laravel permissions
sudo chown -R $USER:www-data storage
sudo chown -R $USER:www-data bootstrap/cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## 📚 Local Development Commands

```bash
# Start development server
php artisan serve --host=0.0.0.0 --port=8000

# Watch for changes (if using Laravel Mix)
npm run watch

# Run background queue
php artisan queue:work

# Run scheduler (in crontab)
* * * * * cd /path/to/laravel-redis-cache && php artisan schedule:run

# Clear all caches
php artisan optimize:clear

# Cache configurations for production
php artisan optimize
```

## 🎯 Production Deployment (Local)

### 1. Optimize for Production
```bash
# Install production dependencies only
composer install --optimize-autoloader --no-dev

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

### 2. Setup Process Management
```bash
# Install Supervisor (Ubuntu)
sudo apt install supervisor

# Create supervisor config
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/laravel-redis-cache/artisan queue:work
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/laravel-redis-cache/storage/logs/worker.log
```

```bash
# Update supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

**✅ Setup lokal selesai! Aplikasi siap untuk development tanpa Docker.**