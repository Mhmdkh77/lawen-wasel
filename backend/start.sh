#!/bin/bash

# Install PHP dependencies
if command -v composer &> /dev/null; then
  echo "Running composer install..."
  composer install --no-interaction --prefer-dist --optimize-autoloader || { echo "Composer install failed"; exit 1; }
else
  echo "⚠️ Composer not installed; please install Composer."
  exit 1
fi

# Build frontend assets (if Node is available)
if command -v npm &> /dev/null; then
  echo "Running Vite build..."
  npm install
  npm run build || { echo "Vite build failed"; exit 1; }
else
  echo "⚠️ Node not installed; skipping Vite build."
fi

# Clear caches to avoid stale asset references
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

php artisan config:cache

# Start Laravel server
php artisan serve --host=0.0.0.0 --port=${PORT}
