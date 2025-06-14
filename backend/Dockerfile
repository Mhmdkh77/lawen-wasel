# Use a robust PHP-FPM base image that's Debian-based (so apt-get works)
FROM php:8.2-fpm # Or php:8.3-fpm, or whatever version matches your local PHP

# Install system dependencies for PostgreSQL, Git, and other PHP extensions
# libpq-dev is for pdo_pgsql
# git is for Composer's source downloads
# libzip-dev is for the PHP zip extension
RUN apt-get update && apt-get install -y libpq-dev git libzip-dev \
    # Install PHP extensions
    # pdo, pdo_pgsql for PostgreSQL
    # zip for Composer's dist downloads
    # Add other common extensions just in case: mbstring, exif, bcmath, intl, fileinfo
    && docker-php-ext-install pdo pdo_pgsql zip mbstring exif bcmath intl fileinfo \
    # Enable extensions (some might need explicit enabling)
    && docker-php-ext-enable pdo_pgsql zip \
    && rm -rf /var/lib/apt/lists/*

# Set the working directory inside the container
WORKDIR /app

# Copy composer.json and composer.lock first to leverage Docker layer caching
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
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# === Choose ONE of the following options for starting your application ===
# OPTION A: Simple development server (recommended for testing)
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]

# OPTION B: PHP-FPM for a more production-like setup (requires a separate web server like Nginx/Caddy on Railway)
# EXPOSE 9000
# CMD ["php-fpm"]
