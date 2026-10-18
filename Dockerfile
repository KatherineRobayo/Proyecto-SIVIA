FROM richarvey/nginx-php-fpm:3.1.6

# 1. Nos movemos a una carpeta temporal que NO sea un volumen
WORKDIR /tmp/project

# 2. Copiamos todo el proyecto a la carpeta temporal
COPY . .

# 3. Instalamos las dependencias de PHP (Composer) DENTRO de la carpeta temporal
RUN composer install --no-dev --optimize-autoloader

# 4. Movemos todo el contenido (incluyendo la carpeta vendor ya instalada) a la carpeta final
RUN mv /tmp/project/* /var/www/html/ && \
    mv /tmp/project/.* /var/www/html/ 2>/dev/null || true

# 5. Configuramos las variables de entorno para producción
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr
ENV COMPOSER_ALLOW_SUPERUSER 1

# 6. Comando de inicio
CMD ["/start.sh"]