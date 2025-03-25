# FROM php:8.2-fpm

# RUN apt-get update && apt-get install -y \
#     git \
#     curl \
#     libpng-dev \
#     libonig-dev \
#     libxml2-dev \
#     zip \
#     unzip

# RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# WORKDIR /var/www/CWF-BB

# COPY . .

# RUN composer install --optimize-autoloader --no-dev

# RUN chown -R www-data:www-data /var/www/CWF-BB

# CMD ["php-fpm"]

FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/CWF-BB

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Install dependencies (no-dev for production)
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-progress

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data \
    /var/www/CWF-BB/storage \
    /var/www/CWF-BB/bootstrap/cache

# Optimize configuration loading
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

CMD ["php-fpm"]
