FROM composer:2 AS vendor

WORKDIR /app

RUN apk add --no-cache icu-dev \
    && docker-php-ext-install intl

COPY composer.json composer.lock ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY artisan ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM node:22-alpine AS frontend

WORKDIR /app

RUN apk add --no-cache \
    php84 \
    php84-bcmath \
    php84-curl \
    php84-ctype \
    php84-dom \
    php84-fileinfo \
    php84-gd \
    php84-iconv \
    php84-intl \
    php84-mbstring \
    php84-openssl \
    php84-pdo \
    php84-pdo_mysql \
    php84-pdo_sqlite \
    php84-phar \
    php84-session \
    php84-simplexml \
    php84-sodium \
    php84-tokenizer \
    php84-xml \
    php84-xmlreader \
    php84-xmlwriter \
    php84-zip \
    && ln -s /usr/bin/php84 /usr/local/bin/php

COPY --from=vendor /app /app
COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.ts tsconfig.json components.json ./
COPY public ./public
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache \
    && php artisan wayfinder:generate --with-form -vvv \
    && npm run build

FROM dunglas/frankenphp:1-php8.3-alpine AS runtime

WORKDIR /app

RUN install-php-extensions \
    bcmath \
    exif \
    gd \
    intl \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_sqlite \
    zip

COPY --from=vendor /app /app
COPY --from=frontend /app/public/build /app/public/build
COPY storage/app/public/templates /app/storage/app/public/templates

RUN mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && rm -rf public/storage \
    && ln -s ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    OCTANE_SERVER=frankenphp \
    SERVER_NAME=:8080

EXPOSE 8080

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
