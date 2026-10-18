#!/usr/bin/env bash

echo "Instalando dependencias..."
composer install --no-dev --working-dir=/var/www/html

echo "Generando clave de aplicación..."
php artisan key:generate --show

echo "Limpiando caché..."
php artisan config:cache
php artisan route:cache

echo "Corriendo migraciones..."
php artisan migrate --force