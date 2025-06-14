# Use a robust PHP-FPM base image that's Debian-based (so apt-get works)
# This is recommended for stability and easy package management
FROM php:8.2-fpm

# Install system dependencies for PostgreSQL and PHP extensions
# These are the lines that install the missing driver!
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && docker-php-ext-enable pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Set the working directory inside the container
WORKDIR /app

# Copy composer.json and composer.lock first to leverage Docker layer caching
# This speeds up future builds if dependencies don't change
COPY composer.json composer.lock ./

# Install Composer (PHP dependency manager)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP dependencies as defined in composer.json/lock
RUN composer install --no-dev --optimize-autoloader

# Copy npm files (package.json, package-lock.json) for caching
COPY package.json package-lock.json ./

# Install Node.js dependencies
RUN npm ci

# Copy the rest of your application code
COPY . .

# Build frontend assets using Vite
RUN npm run build

# Set appropriate permissions for Laravel's storage and cache directories
# 'www-data' is the typical user for PHP-FPM on Debian-based images
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# === Choose ONE of the following options for starting your application ===
# OPTION A: Simple development server (recommended for testing)
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]

# OPTION B: PHP-FPM for a more production-like setup (requires a separate web server like Nginx/Caddy on Railway)
# EXPOSE 9000
# CMD ["php-fpm"]
