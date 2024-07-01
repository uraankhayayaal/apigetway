FROM composer:latest as vendor
  
COPY ./src/composer.json /app/composer.json
COPY ./src/composer.lock /app/composer.lock
 
RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

FROM php:7.2-apache-stretch
 
COPY ./conf/php /usr/local/etc/php/conf.d
COPY ./src /app
COPY --from=vendor /app/vendor/ /app/vendor/