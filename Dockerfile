# Imagen para Railway (y cualquier host con variable PORT).
# Document root: public/ (mismo criterio que en README con php -S).
FROM php:8.2-cli-bookworm

RUN docker-php-ext-install pdo_mysql \
    && rm -rf /tmp/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json ./
RUN composer install --no-dev --no-interaction --no-scripts

COPY . .

RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 8080

# Railway inyecta PORT; el servidor integrado de PHP escucha en todas las interfaces.
CMD ["sh", "-c", "exec php -S 0.0.0.0:${PORT:-8080} -t /var/www/html/public"]
