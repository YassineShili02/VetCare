# Stage 1: Build
FROM composer:2 AS builder
WORKDIR /app

# 1. Copy ONLY composer files first (for layer caching)
COPY composer.json composer.lock ./

# 2. Install deps WITHOUT running Symfony scripts (bin/console doesn't exist yet)
RUN composer install --no-dev --optimize-autoloader --classmap-authoritative --no-scripts

# 3. NOW copy the rest of the project
COPY . .

# 4. Dump autoloader with project classes now that code is present
RUN composer dump-autoload --optimize --classmap-authoritative

# 5. Compile assets (AssetMapper for Symfony 6.4)
RUN php bin/console asset-map:compile --env=prod

# 6. Clear and warm up cache
RUN php bin/console cache:clear --env=prod && php bin/console cache:warmup --env=prod

# Stage 2: Runtime (PHP-FPM + Nginx)
FROM php:8.2-fpm-alpine
RUN apk add --no-cache nginx mysql-client \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=builder /app /var/www/html
COPY nginx.conf /etc/nginx/nginx.conf

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/var \
    && chmod -R 775 /var/www/html/var/cache /var/www/html/var/log

EXPOSE 80
USER www-data
CMD ["sh", "-c", "nginx && php-fpm"]