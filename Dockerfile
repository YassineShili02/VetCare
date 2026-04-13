# Stage 1: Build
FROM composer:2 AS builder
WORKDIR /app

# 1. Copy ONLY composer files first (for layer caching)
COPY composer.json composer.lock ./

# 2. Install PHP deps WITHOUT running Symfony scripts
RUN composer install --no-dev --optimize-autoloader --classmap-authoritative --no-scripts

# ✅ Create AssetMapper structure BEFORE copying project files
# (Dummies act as fallback; real files from COPY . . will overwrite them)
RUN mkdir -p /app/assets/controllers /app/assets/styles /app/assets/vendor && \
    echo '{"controllers":{}}' > /app/assets/controllers.json && \
    echo "<?php\nreturn [];" > /app/assets/importmap.php && \
    echo "// Entry point" > /app/assets/app.js

# 3. NOW copy the rest of the project (real files overwrite dummies)
COPY . .

# 4. Dump autoloader with project classes
RUN composer dump-autoload --optimize --classmap-authoritative

# ✅ Set dummy env vars for build-time Symfony commands
ENV DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db?serverVersion=3.15"
ENV APP_SECRET="build-time-dummy"
ENV APP_ENV="prod"

# 5. Install Importmap dependencies (Stimulus, etc.)
RUN php bin/console importmap:install --env=prod

# 6. Compile assets with AssetMapper
RUN php bin/console asset-map:compile --env=prod

# 7. Clear and warm up cache
RUN php bin/console cache:clear --env=prod && php bin/console cache:warmup --env=prod

# Stage 2: Runtime
FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx mysql-client icu-dev \
    && docker-php-ext-install -j$(nproc) pdo_mysql intl \
    && apk del icu-dev

COPY --from=builder /app /var/www/html
COPY nginx.conf /etc/nginx/nginx.conf
COPY docker/entrypoint.sh /entrypoint.sh

RUN mkdir -p /var/lib/nginx/tmp /var/log/nginx /run/nginx \
    && mkdir -p /var/www/html/var/cache /var/www/html/var/log \
    && chown -R www-data:www-data /var/lib/nginx /var/log/nginx /run/nginx \
    && chown -R www-data:www-data /var/www/html \
    && chmod +x /entrypoint.sh \
    && chmod -R 775 /var/www/html/var

EXPOSE 8080
USER www-data
CMD ["/entrypoint.sh"]
