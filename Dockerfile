# PHP application
FROM dunglas/frankenphp:1-php8.4-alpine AS base

RUN install-php-extensions opcache intl

COPY packages/demo/docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY packages/demo/docker/Caddyfile /etc/caddy/Caddyfile

WORKDIR /app

# Install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy bundle first (it's a dependency) - must match path in demo's composer.json
COPY packages/htmx-bundle ../htmx-bundle

# Copy demo composer files and install dependencies
COPY packages/demo/composer.json ./
RUN composer update --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Copy demo source
COPY packages/demo/ .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --classmap-authoritative

# Install importmap vendor assets and compile assets for production
RUN php bin/console importmap:install && \
    php bin/console asset-map:compile

# Set permissions
RUN mkdir -p /app/var && chown -R www-data:www-data /app/var

# Configure FrankenPHP with worker mode
ENV SERVER_NAME=:80
ENV APP_RUNTIME=Runtime\\FrankenPhpSymfony\\Runtime
ENV FRANKENPHP_CONFIG="worker ./public/index.php 500"

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
