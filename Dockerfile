FROM php:8.3-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install --no-scripts

COPY . .
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache
RUN composer dump-autoload
