FROM php:7.4-apache

ENV APP_ENV=prod

RUN apt-get update \
    && apt-get install -y -qq \
        git \
        unzip \
    && docker-php-ext-install \
        pdo_mysql \
    && yes '' | pecl install -f redis \
    && docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY apache/site.conf /etc/apache2/sites-available/000-default.conf
COPY apache/ports.conf /etc/apache2/ports.conf

WORKDIR /var/www/html

COPY composer.* ./

RUN composer i -n --no-dev --no-scripts

COPY . ./

RUN composer i -n --no-dev -o \
    && chown -R www-data:www-data var

ENV PORT=80
