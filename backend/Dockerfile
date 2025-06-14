FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    mbstring \
    zip \
    exif \
    bcmath \
    intl \
    fileinfo

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Install Laravel dependencies
RUN composer install

# Make sure storage and bootstrap/cache are writable
RUN chmod -R 775 storage bootstrap/cache

# Expose the Laravel port
EXPOSE 8080

# Run Laravel development server
CMD php artisan serve --host=0.0.0.0 --port=8080
