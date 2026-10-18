FROM richarvey/nginx-php-fpm:3.1.6

# Copiamos el proyecto
COPY . /var/www/html

# Configuramos el entorno para Laravel
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Permitimos que composer corra como root
ENV COMPOSER_ALLOW_SUPERUSER 1

# Comando de inicio (la imagen base ya se encarga del resto)
CMD ["/start.sh"]