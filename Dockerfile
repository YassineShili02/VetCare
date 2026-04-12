# Stage 1: Build
FROM composer:2 AS builder
WORKDIR /app

# 1. Copy ONLY composer files first (for layer caching)
COPY composer.json composer.lock ./

# 2. Install PHP deps WITHOUT running Symfony scripts
RUN composer install --no-dev --optimize-autoloader --classmap-authoritative --no-scripts

# 3. NOW copy the rest of the project
COPY . .

# 4. Dump autoloader with project classes
RUN composer dump-autoload --optimize --classmap-authoritative

ENV DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db?serverVersion=3.15"
ENV APP_SECRET="build-time-dummy"
ENV APP_ENV="prod"
# 5.  Install Importmap dependencies (Stimulus, etc.)
RUN php bin/console importmap:install --env=prod

# 6.  Compile assets with AssetMapper
RUN php bin/console asset-map:compile --env=prod

# 7. Clear and warm up cache
RUN php bin/console cache:clear --env=prod && php bin/console cache:warmup --env=prod

# Stage 2: Runtime (PHP-FPM + Nginx)
FROM php:8.2-fpm-alpine

# Install runtime deps + ICU dev headers for intl extension
RUN apk add --no-cache nginx mysql-client icu-dev \
    && docker-php-ext-install -j$(nproc) pdo_mysql intl \
    && apk del icu-dev  # Remove dev headers after build to keep image small

COPY --from=builder /app /var/www/html
COPY nginx.conf /etc/nginx/nginx.conf

# Fix permissions for www-data (UID 33 in Alpine)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/var \
    && chmod -R 775 /var/www/html/var/cache /var/www/html/var/log

EXPOSE 80
USER www-data
CMD ["sh", "-c", "nginx && php-fpm"]