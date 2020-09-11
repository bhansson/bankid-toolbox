# syntax = docker/dockerfile:1.0-experimental

FROM php:7.4-fpm-alpine

# Install dependencies
RUN apk add --update --no-cache

COPY ./.docker/php.ini /usr/local/etc/php/php.ini

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /tmp
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Application
ARG APPLICATION_ENVIRONMENT
WORKDIR /app

# Copy files that rarely change
COPY --chown=www-data:www-data composer.json .
COPY --chown=www-data:www-data composer.lock .

# Install composer packages without autoload (source does not exist yet)
ARG COMPOSER_INSTALL=on
ENV COMPOSER_CACHE_DIR /cache/composer
RUN --mount=type=cache,target=/cache/composer \
    if [ "${COMPOSER_INSTALL}" = "on" ] && [ "${APPLICATION_ENVIRONMENT}" = "development" ]; then \
        composer install --no-interaction --no-scripts --no-plugins --no-autoloader; \
    elif [ "${COMPOSER_INSTALL}" = "on" ]; then \
        composer install --no-interaction --no-scripts --no-plugins --no-autoloader --quiet --no-dev; \
    fi

# Copy files that often change
COPY --chown=www-data:www-data ./.docker/bin ./bin

# Generate autoload.php with new files
RUN if [ "${COMPOSER_INSTALL}" = "on" ] && [ "${APPLICATION_ENVIRONMENT}" = "development" ]; then \
        composer dump-autoload; \
    elif [ "${COMPOSER_INSTALL}" = "on" ]; then \
        composer dump-autoload --quiet --optimize; \
    fi

