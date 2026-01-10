#!/bin/bash

# Deployment optimization script
echo "🚀 Optimizing Filament Application..."

# Clear all caches first
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize

# Composer optimization
composer dump-autoload -o

echo "✅ Optimization completed!"
