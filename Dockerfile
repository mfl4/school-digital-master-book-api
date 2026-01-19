# =============================================================================
# Dockerfile untuk Laravel 12 API - School Digital Master Book
# Optimized untuk Railway Deployment
# =============================================================================

FROM php:8.4-cli

# Tentukan working directory
WORKDIR /app

# Instal ekstensi dan dependensi
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instal Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Salin file composer
COPY composer.json composer.lock ./

# Instal PHP dependencies tanpa dev packages
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Salin file package.json dan package-lock.json
COPY package.json package-lock.json* ./

# Instal Node.js dependencies dan build assets
RUN npm install && npm run build

# Salin semua file aplikasi
COPY . .

# Jalankan dump-autoload untuk optimasi
RUN composer dump-autoload --optimize

# Set izin direktori storage dan bootstrap/cache
RUN mkdir -p storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Generate dokumentasi API dengan l5-swagger
RUN php artisan l5-swagger:generate || true

# Konfigurasi cache aplikasi
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

# Ekspos PORT untuk Railway
EXPOSE ${PORT:-8000}

# Mulai aplikasi dengan Artisan serve
# Set PORT environment variable secara otomatis
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}