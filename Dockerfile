FROM php:8.2-cli-alpine

# Install system dependencies and build libraries
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    nodejs \
    npm \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    icu-dev \
    oniguruma-dev \
    mariadb-client

# Configure and install PHP extensions needed by Laravel, DomPDF, Excel, and QR
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql gd bcmath zip intl pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy codebase
COPY . .

# Install PHP dependencies with --no-scripts to prevent premature artisan execution
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Generate optimized autoload dump
RUN composer dump-autoload --optimize

# Install NPM dependencies and build Vite frontend assets
RUN npm install && npm run build

# Fix folder permissions for storage and cache
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD php artisan storage:link --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
