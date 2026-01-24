FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache \
    nginx \
    supervisor \
    && docker-php-ext-install opcache

COPY packages/demo/docker/nginx.conf /etc/nginx/nginx.conf
COPY packages/demo/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY packages/demo/docker/php.ini /usr/local/etc/php/conf.d/custom.ini

WORKDIR /app

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy bundle first (it's a dependency) - must match path in demo's composer.json
COPY packages/htmx-bundle ../htmx-bundle

# Copy demo composer files and install dependencies
COPY packages/demo/composer.json packages/demo/composer.lock* ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Copy demo source
COPY packages/demo/ .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --classmap-authoritative

# Set permissions
RUN chown -R www-data:www-data /app/var 2>/dev/null || mkdir -p /app/var && chown -R www-data:www-data /app/var

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
