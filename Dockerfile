FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    autoconf \
    build-base \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    icu-dev \
    mysql-client \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    freetype \
    libjpeg-turbo

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli mbstring exif zip

# Install GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create app directory
WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Fix php-fpm listen address for cross-container access
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-listen.conf

# Create required storage directories
RUN mkdir -p storage/framework/cache/data \
    storage/framework/cache/locks \
    storage/framework/views \
    storage/framework/sessions \
    storage/logs \
    storage/app/public \
    bootstrap/cache

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
