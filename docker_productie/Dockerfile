FROM php:apache

RUN apt-get update && apt-get install -y  libxml2-dev
RUN docker-php-ext-install mysqli pdo pdo_mysql soap
RUN docker-php-ext-enable pdo_mysql soap
RUN a2enmod rewrite

COPY ./php.ini /usr/local/etc/php/php.ini

# open docker desktop run on terminal
# docker build . -t 301167/php-apache-pdo:latest
# docker push 301167/php-apache-pdo:latest