# Usamos la imagen oficial de PHP (versión 8.2, que es compatible con Laravel 12)
FROM php:8.2-cli

# Instalamos las dependencias del sistema necesarias para PHP y Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Instalamos las extensiones de PHP
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd

# Instalamos Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiamos el proyecto al contenedor
WORKDIR /app
COPY . .

# Instalamos las dependencias sin las de desarrollo (producción)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Damos permisos a las carpetas de storage y cache
RUN chmod -R 775 storage bootstrap/cache

# Exponemos el puerto (Render usa el 10000 por defecto)
EXPOSE 10000

# El comando que inicia la aplicación en Render
CMD php artisan serve --host=0.0.0.0 --port=10000