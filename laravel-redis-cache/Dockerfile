FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install system dependencies in smaller groups to avoid conflicts
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

# Install development dependencies
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    autoconf \
    g++ \
    make \
    oniguruma-dev \
    libxml2-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    icu-dev

# Configure GD extension
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    sockets \
    opcache \
    intl

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Clean up build dependencies
RUN apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create www-data user if not exists
RUN addgroup -g 1000 -S www-data || true \
    && adduser -u 1000 -S www-data -G www-data || true

# Copy application files
COPY --chown=www-data:www-data . /var/www

# Set proper permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Copy PHP configuration
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Switch to www-data user
USER www-data

# Install Composer dependencies
RUN composer install --optimize-autoloader --no-scripts

# Switch back to root for final setup
USER root

# Create required directories
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /var/www/storage/logs \
    && chown -R www-data:www-data /var/www/storage

# Expose port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php-fpm -t || exit 1

# Start PHP-FPM
CMD ["php-fpm"]