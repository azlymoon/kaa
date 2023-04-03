FROM php:8.2-alpine

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN apk update && apk add --no-cache \
    vim \
    bash \
    git

RUN install-php-extensions \
    json \
    iconv \
    intl \
    mbstring \
    tokenizer \
    xdebug \
    @composer

COPY ./xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

ENV PHP_IDE_CONFIG='serverName=kaa-debug'
WORKDIR /var
