FROM composer:2.8 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM node:22-bookworm AS assets

WORKDIR /app

COPY package.json ./
RUN npm install

COPY resources ./resources
COPY vite.config.js .
RUN npm run build

FROM php:8.4-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates libonig-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN cp docker/aiven-ca.pem /usr/local/share/ca-certificates/aiven-ca.crt \
    && update-ca-certificates
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

EXPOSE 8080

ENTRYPOINT ["entrypoint"]