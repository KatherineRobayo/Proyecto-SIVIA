FROM php:8.4-cli

# Instalamos PHP y Node.js
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libpq-dev \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Instalamos dependencias de PHP y de Node, y COMPILAMOS los assets
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs \
    && npm install \
    && npm run build

# Aseguramos que Laravel publique los assets en producción
RUN php artisan vendor:publish --tag=laravel-assets --ansi --force || true

# Damos permisos
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 10000

# Arrancamos el servidor
CMD php artisan config:clear && php artisan view:clear && php artisan migrate:fresh --seed --force && php artisan serve --host=0.0.0.0 --port=10000