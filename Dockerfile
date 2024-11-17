FROM php:8.2-fpm-alpine as build
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
RUN curl -sS https://getcomposer.org/installer | php -- --filename=composer --install-dir=/usr/local/bin
RUN apk add --no-cache git
RUN git config --global url."https://{GITHUB_API_TOKEN}:@github.com/".insteadOf "https://github.com/"
COPY ./composer.json /app/composer.json
COPY ./composer.lock /app/composer.lock
WORKDIR /app

FROM build as dev_deps
RUN composer install

FROM dev_deps as test
COPY ./ /app
RUN ./vendor/bin/php-cs-fixer fix app --allow-risky=yes
RUN ./vendor/bin/phpstan analyse app tests
RUN ./vendor/bin/phpunit
RUN rm -rf ./vendor

FROM build as prod_deps
RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader

FROM prod_deps as prod
COPY --from=test ./app /app