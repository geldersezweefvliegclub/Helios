FROM php:8.1.12-apache

COPY ../ /var/www/html/
COPY ./php.ini /usr/local/etc/php/php.ini

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql
RUN a2enmod rewrite

