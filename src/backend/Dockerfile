FROM php:8.3-apache

RUN docker-php-ext-install mysqli

WORKDIR /var/www/html

COPY public/ /var/www/html/
COPY src/ /var/www/src/
COPY config/ /var/www/config/

EXPOSE 80
