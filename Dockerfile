FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libpq-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 10000

# ESTA ES LA MAGIA: Creamos un script que corre las migraciones y luego enciende el servidor
CMD php artisan migrate:fresh --seed --force && php artisan serve --host=0.0.0.0 --port=10000 &
