#!/bin/bash

# Deployment script for MicroNET Sales
# Run this script on your DigitalOcean droplet after pulling latest code

set -e  # Exit on error

echo "🚀 Starting deployment..."

cd /var/www/micronetops

# Pull latest code
echo "📥 Pulling latest code..."
sudo -u www-data git pull origin main

# Install/update dependencies
echo "📦 Installing PHP dependencies..."
sudo -u www-data composer install --optimize-autoloader --no-dev

echo "📦 Installing Node dependencies..."
sudo -u www-data npm install

echo "🔨 Building assets..."
sudo -u www-data npm run build

# Run migrations
echo "🗄️  Running migrations..."
sudo -u www-data php artisan migrate --force

# Clear and cache
echo "⚡ Optimizing application..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Set permissions
echo "🔐 Setting permissions..."
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache

echo "✅ Deployment completed successfully!"
echo "🌐 Your application should now be updated."

