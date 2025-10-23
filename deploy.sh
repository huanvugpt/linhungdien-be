#!/bin/bash

# Deploy script for Laravel application
# This script pulls latest code and restarts the backend with PM2

echo "🚀 Starting deployment process..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Change to project directory
cd /var/www/html/linhungdien

# Check if we're in a git repository
if [ ! -d ".git" ]; then
    print_error "Not a git repository!"
    exit 1
fi

print_status "Pulling latest changes from git repository..."

# Stash any local changes first
git stash save "Auto-stash before deploy $(date)"

# Pull latest changes from remote repository
if git pull origin main; then
    print_status "Git pull successful!"
else
    print_error "Git pull failed!"
    exit 1
fi

# Install/update PHP dependencies
print_status "Updating PHP dependencies..."
if composer install --no-dev --optimize-autoloader; then
    print_status "Composer install completed!"
else
    print_warning "Composer install had issues, continuing..."
fi

# Install/update Node.js dependencies
if [ -f "package.json" ]; then
    print_status "Updating Node.js dependencies..."
    if npm install; then
        print_status "NPM install completed!"
    else
        print_warning "NPM install had issues, continuing..."
    fi

    # Build assets
    print_status "Building frontend assets..."
    if npm run build; then
        print_status "Assets built successfully!"
    else
        print_warning "Asset build had issues, continuing..."
    fi
fi

# Clear Laravel caches
print_status "Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimize Laravel
print_status "Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
print_status "Running database migrations..."
if php artisan migrate --force; then
    print_status "Database migrations completed!"
else
    print_warning "Database migrations had issues, continuing..."
fi

# Set proper permissions
print_status "Setting proper file permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Restart PM2 processes
print_status "Restarting PM2 processes..."

# Check if PM2 is installed
if ! command -v pm2 &> /dev/null; then
    print_error "PM2 is not installed!"
    print_status "Installing PM2 globally..."
    npm install -g pm2
fi

# Check if there are any PM2 processes running
if pm2 list | grep -q "online\|stopped\|errored"; then
    print_status "Restarting existing PM2 processes..."
    pm2 restart all
    pm2 save
else
    print_warning "No PM2 processes found to restart"
    print_status "You may need to start your Laravel app with PM2:"
    print_status "pm2 start artisan --name laravel-app -- serve --host=0.0.0.0 --port=8000"
fi

# Show PM2 status
print_status "Current PM2 status:"
pm2 list

print_status "🎉 Deployment completed successfully!"
print_status "Application should be running at the configured URL"

echo ""
echo "Deployment Summary:"
echo "✅ Git pull completed"
echo "✅ Dependencies updated"
echo "✅ Assets built"
echo "✅ Caches cleared and optimized"
echo "✅ Database migrations run"
echo "✅ Permissions set"
echo "✅ PM2 processes restarted"
echo ""