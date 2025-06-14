#!/bin/bash

# Build frontend assets (if Node is available)
if command -v npm &> /dev/null; then
  echo "Running Vite build..."
  npm install
  npm run build
else
  echo "⚠️ Node not installed; skipping Vite build."
fi

# Laravel boot
php artisan config:cache
php artisan serve --host=0.0.0.0 --port=${PORT}
