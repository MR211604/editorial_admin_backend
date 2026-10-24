# syntax=docker/dockerfile:1
FROM php:5.6-apache

# Repos archivados de Debian Stretch + tolerar claves/Release expirados
RUN sed -i 's/deb.debian.org/archive.debian.org/g; s|security.debian.org|archive.debian.org|g' /etc/apt/sources.list \
    && sed -i '/stretch-updates/d' /etc/apt/sources.list \
    && printf '%s\n' \
        'Acquire::Check-Valid-Until "false";' \
        'Acquire::AllowInsecureRepositories "true";' \
        'Acquire::AllowDowngradeToInsecureRepositories "true";' \
        'APT::Get::AllowUnauthenticated "true";' \
        > /etc/apt/apt.conf.d/99legacy

RUN apt-get update && apt-get install -y --no-install-recommends --allow-unauthenticated \
        git \
        unzip \
        zip \
        libmcrypt-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libxml2-dev \
        zlib1g-dev \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install \
        mcrypt \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        opcache \
    && rm -rf /var/lib/apt/lists/*

# Composer 2.2 LTS (última versión de Composer 2 compatible con PHP 5.6)
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# Apache
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Clonar repo privado con secret de BuildKit (el token no queda en la imagen)
RUN --mount=type=secret,id=gh_token \
    find /var/www/html -mindepth 1 -delete \
    && git clone https://x-access-token:$(cat /run/secrets/gh_token)@github.com/MR211604/cms-udb-editorial.git . \
    && git remote set-url origin https://github.com/MR211604/cms-udb-editorial.git \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    && cp .env.example .env \
    && php artisan key:generate

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
