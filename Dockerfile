FROM php:8.2-fpm-alpine as vendor
RUN curl -sS https://getcomposer.org/installer | php -- --filename=composer --install-dir=/usr/local/bin
RUN apk add --no-cache git
RUN git config --global url."https://{GITHUB_API_TOKEN}:@github.com/".insteadOf "https://github.com/"
COPY ./composer.json /app/composer.json
COPY ./composer.lock /app/composer.lock
WORKDIR /app
RUN composer install

FROM vendor as dev
RUN apk update && \
    apk add bash build-base gcc autoconf libmcrypt-dev \
    g++ make openssl-dev \
    php-openssl \
    php-bcmath \
    php-curl \
    php-tokenizer \
    php-json \
    php-xml \
    php-zip \
    php-pdo_mysql \
    php-mbstring
COPY ./php.ini /usr/local/etc/php/conf.d/base.ini