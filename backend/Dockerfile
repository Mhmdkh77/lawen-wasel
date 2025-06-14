FROM php:8.2-cli

# Install system dependencies & PostgreSQL driver support
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    curl \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql zip mbstring exif bcmath intl fileinfo

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy Laravel project
COPY . .

# Install Laravel dependencies
RUN composer install

# Ensure correct file permissions
RUN chmod -R 775 storage bootstrap/cache

# Expose port for Railway
EXPOSE 8080

# Final run command using array syntax to avoid CMD errors
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
