FROM composer:latest as composer
FROM php:8.2-fpm-alpine as php

RUN set -eux; \
    apk update; \
    apk upgrade; \
    apk add --no-cache \
        curl \
        sudo \
        postgresql-dev \
        libzip \
        libjpeg-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libxpm-dev \
        libmcrypt-dev \
        libzip-dev \
        icu-dev \
        zip \
        unzip \
        freetype-dev \
        oniguruma-dev \
        libxml2-dev \
        linux-headers \
        build-base \
        mysql-client \
        mariadb-dev; \
    rm -rf /var/cache/apk/*

RUN set -eux; \
    docker-php-ext-configure pdo_mysql; \
    docker-php-ext-install pdo_mysql; \
    docker-php-ext-configure gd \
        --with-jpeg \
        --with-webp \
        --with-xpm \
        --with-freetype; \
    docker-php-ext-install gd; \
    php -r 'var_dump(gd_info());'

RUN set -eux; \
    docker-php-ext-install intl exif zip bcmath sockets;

ENV XDEBUG_CONFIG="client_host=host.docker.internal client_port=9001 start_with_request=yes"
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_MEMORY_LIMIT=-1

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
RUN /usr/local/bin/composer self-update

RUN addgroup -g 1000 www && adduser -u 1000 -S -G www www
USER www

EXPOSE 9001

WORKDIR /var/www/app
