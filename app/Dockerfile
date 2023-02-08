FROM php:8.2.0-apache-bullseye

RUN echo 'Acquire::http::Proxy "http://proxy.vmv.group:3128";' > /etc/apt/apt.conf

RUN apt-get update \
    && apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

WORKDIR /var/www/html

COPY ./html/ /var/www/html/
