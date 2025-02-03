# Use the official PHP image with FPM
FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/CWF-BB

# Copy Laravel files
COPY . .

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions for Laravel storage and bootstrap cache
RUN chown -R www-data:www-data /var/www/CWF-BB/storage /var/www/CWF-BB/bootstrap/cache
RUN chmod -R 775 /var/www/CWF-BB/storage /var/www/CWF-BB/bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
