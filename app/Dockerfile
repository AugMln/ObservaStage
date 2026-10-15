FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev libzip-dev libicu-dev libpng-dev unzip \
    && docker-php-ext-install pdo_pgsql zip intl gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN { \
      echo "memory_limit=512M"; \
      echo "upload_max_filesize=20M"; \
      echo "post_max_size=25M"; \
      echo "max_execution_time=120"; \
    } > /usr/local/etc/php/conf.d/observastage.ini

WORKDIR /var/www
COPY composer.json ./
RUN composer install --no-dev --no-interaction --prefer-dist
