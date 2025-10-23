#!/bin/bash

# Quick deploy script
echo "🚀 Quick Deploy Starting..."

cd /var/www/html/linhungdien

# Pull latest code
echo "📥 Pulling latest code..."
git pull origin main

# Install dependencies if needed
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader
fi

# Clear and cache Laravel
echo "🔄 Refreshing Laravel caches..."
php artisan config:clear && php artisan config:cache
php artisan route:clear && php artisan route:cache
php artisan view:clear && php artisan view:cache

# Run migrations
echo "📊 Running migrations..."
php artisan migrate --force

# Restart PM2
echo "🔄 Restarting PM2..."
pm2 restart all

echo "✅ Quick deploy completed!"
pm2 list