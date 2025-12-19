FROM php:8.2-apache

RUN a2enmod rewrite \
 && sed -i 's/AllowOverride None/AllowOverride All/i' /etc/apache2/apache2.conf

RUN apt-get update && apt-get install -y --no-install-recommends \
      default-mysql-client \
  && rm -rf /var/lib/apt/lists/* \
  && docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html
COPY . .
