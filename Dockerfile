FROM php:8.2-apache

COPY . /var/www/html/

RUN docker-php-ext-install mysqli

# FIX ERROR MPM (WAJIB)
RUN a2dismod mpm_event && a2enmod mpm_prefork