# Usamos la imagen oficial de PHP 8.4 (la que pide tu proyecto)
FROM php:8.4-cli

# Instalamos dependencias del sistema y extensiones de PHP necesarias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Instalamos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiamos el proyecto
WORKDIR /app
COPY . .

# Instalamos las dependencias (sin dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Permisos para storage y cache
RUN chmod -R 777 storage bootstrap/cache

# Exponemos el puerto 10000 (el que usa Render)
EXPOSE 10000

# Comando para correr el servidor
CMD php artisan serve --host=0.0.0.0 --port=10000