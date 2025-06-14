# Use a robust PHP-FPM base image. This ensures apt-get is available.
# You can change '8.2' to '8.3' if your local PHP version is 8.3.
FROM php:8.2-fpm

# Install essential system dependencies and PHP extensions for Laravel:
# - libpq-dev: Development libraries for PostgreSQL (needed by pdo_pgsql)
# - git: Required by Composer for downloading some packages from source
# - libzip-dev: Required for the PHP zip extension (Composer's dist downloads)
# - common PHP extensions: mbstring, exif, bcmath, intl, fileinfo are frequently used by Laravel packages.
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    libzip-dev \
    # Clean up apt cache to keep image size down
    && rm -rf /var/lib/apt/lists/* \
    \
    # Install PHP extensions using the official docker-php-ext-install helper
    # pdo and pdo_pgsql are for PostgreSQL database connection
    # zip is crucial for Composer downloads
    && docker-php-ext-install -j$(nproc) \
    pdo pdo_pgsql \
    zip \
    mbstring \
    exif \
    bcmath \
    intl \
    fileinfo \
    \
    # Enable extensions (some might need explicit enabling)
    && docker-php-ext-enable pdo_pgsql zip

# Set the working directory inside the container to /app
WORKDIR /app

# Copy composer.json and composer.lock first
# This allows Docker to cache the Composer dependencies layer, speeding up subsequent builds
COPY composer.json composer.lock ./

# Install Composer (PHP dependency manager)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP dependencies as defined in composer.json/lock
# --no-dev: Excludes development dependencies for a smaller production image
# --optimize-autoloader: Optimizes Composer's autoloader for faster execution
RUN composer install --no-dev --optimize-autoloader

# Copy Node.js dependency files for caching
COPY package.json package-lock.json ./

# Install Node.js dependencies
RUN npm ci

# Copy the rest of your application code into the container
COPY . .

# Build frontend assets using Vite (assuming your package.json has a 'build' script)
RUN npm run build

# Set appropriate ownership and permissions for Laravel's storage and cache directories
# 'www-data' is the default user for PHP-FPM processes on Debian-based images
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# --- Choose ONE of the following options for starting your application ---
# OPTION A: For simple development/testing using Laravel's built-in development server
# This is usually what you want for a single-service app on Railway for testing.
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host", "0.0.0.0", "--port", "8000"]

# OPTION B: For a more robust production setup using PHP-FPM
# This option requires you to add a separate Nginx or Caddy service on Railway
# to act as a web server and proxy requests to this PHP-FPM service.
# EXPOSE 9000
# CMD ["php-fpm"]
