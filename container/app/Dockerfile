#------------
# node
#------------
FROM node:lts-slim AS node

ENV TZ=Asia/Tokyo
RUN ln -snf /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && echo 'Asia/Tokyo' > /etc/timezone

COPY --chown=33:33 ./package.json /app/
COPY --chown=33:33 ./webpack.config.js /app/
COPY --chown=33:33 ./gulpfile.js /app/

WORKDIR /app

RUN set -xe && \
    npm install -g npm@10 && \
    npm install && \
    npm start

#COPY --chown=33:33 ../app/assets /app/assets
#COPY --chown=33:33 ../app/bin /app/bin
#COPY --chown=33:33 ../app/includes /app/includes
#COPY --chown=33:33 ../app/public /app/public
#COPY --chown=33:33 ../app/src /app/src
#COPY --chown=33:33 ../app/templates /app/templates


#------------
# php-fpm
#------------
FROM php:8.2-fpm-alpine AS app

RUN addgroup -S nginx && adduser -S nginx -G nginx

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer \
    TZ=Asia/Tokyo
RUN ln -snf /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && echo 'Asia/Tokyo' > /etc/timezone

RUN apk update && \
    apk add --no-cache git wget libzip autoconf linux-headers libtool automake libpng bash build-base icu-dev \
    zlib-dev g++ make automake autoconf libzip-dev libpng-dev unzip

RUN set -xe && \
    docker-php-ext-install intl zip bcmath && \
    pecl install grpc && \
    docker-php-ext-enable grpc

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

COPY --from=node --chown=33:33 /app /app
COPY --chown=33:33 ./composer.json .
COPY --chown=33:33 ./php.ini /usr/local/etc/php/php.ini

RUN set -xe && \
    composer install

EXPOSE 9000
CMD ["php-fpm"]