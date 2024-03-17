FROM php:apache

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql
RUN a2enmod rewrite

# open docker desktop run on terminal
# docker build . -t 301167/php-apache-pdo:latest
# docker push 301167/php-apache-pdo:latest


