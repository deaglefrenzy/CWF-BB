FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN chmod +x /usr/bin/composer

WORKDIR /var/www/CWF-BB

COPY . .

RUN composer install --optimize-autoloader --no-interaction --no-dev || \
    (echo "Composer install failed" && exit 1)

RUN chown -R root:root /var/www/CWF-BB

CMD ["php-fpm"]

