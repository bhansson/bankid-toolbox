# syntax = docker/dockerfile:1.0-experimental

FROM php:8.1-fpm-alpine

# Install dependencies
RUN apk add --update --no-cache

COPY ./.docker/php.ini /usr/local/etc/php/php.ini

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /tmp
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Application
WORKDIR /app

# Copy files that rarely change
COPY --chown=www-data:www-data composer.json .

# Install composer packages without autoload (source does not exist yet)
ENV COMPOSER_CACHE_DIR /cache/composer
RUN --mount=type=cache,target=/cache/composer \
    composer install --no-interaction --no-scripts --no-plugins --no-autoloader;

# Copy files that often change
COPY --chown=www-data:www-data ./.docker/bin ./bin

# Generate autoload.php with new files
RUN composer dump-autoload;

