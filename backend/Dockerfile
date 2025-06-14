FROM php:8.2-cli

# Install required system packages
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    libonig-dev \
    libicu-dev \
    git \
    curl \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        mbstring \
        zip \
        exif \
        bcmath \
        intl \
        fileinfo

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy Laravel files
COPY . .

# Install PHP dependencies
RUN composer install

# Set file permissions
RUN chmod -R 775 storage bootstrap/cache

# Expose the correct port
EXPOSE 8080

# Start Laravel server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
