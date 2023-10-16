#!/usr/bin/env bash
echo "Running composer"
composer global require hirak/prestissimo
composer install --no-dev --working-dir=/var/www/html

echo "Generate Key"
php artisan key:generate --show

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Running migrations..."
php artisan migrate --force
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=UserProfileSeeder

echo "Install passport"
php artisan passport:install
# php artisan serve
echo "Listen to mail queue"
php artisan queue:work --queue=emails
php artisan queue:listen