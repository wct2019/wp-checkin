#------------
# node
#------------
FROM node:lts-slim AS node

ENV TZ=Asia/Tokyo
RUN ln -snf /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && echo 'Asia/Tokyo' > /etc/timezone

RUN mkdir /app
WORKDIR /app

COPY --chown=33:33 ./container/package.json /app/
COPY --chown=33:33 ./container/webpack.config.js .
COPY --chown=33:33 ./container/gulpfile.js .

RUN set -xe && \
    npm install -g npm@10 && \
    npm install && \
    npm start

COPY --chown=33:33 ./assets /app/assets
COPY --chown=33:33 ./bin /app/bin
COPY --chown=33:33 ./includes /app/includes
COPY --chown=33:33 ./public /app/public
COPY --chown=33:33 ./src /app/src
COPY --chown=33:33 ./templates /app/templates
COPY --chown=33:33 ./logs /app/logs


#------------
# php-fpm
#------------
FROM php:8.2-fpm-alpine AS app

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer \
    TZ=Asia/Tokyo
RUN ln -snf /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && echo 'Asia/Tokyo' > /etc/timezone

RUN addgroup -S nginx && adduser -S nginx -G nginx


RUN apk update && \
    apk add --no-cache git wget libzip autoconf linux-headers libtool automake libpng bash build-base icu-dev nginx \
    zlib-dev g++ make automake autoconf libzip-dev libpng-dev unzip

RUN set -xe && \
    docker-php-ext-install intl zip bcmath && \
    pecl install grpc && \
    docker-php-ext-enable grpc

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

#COPY --from=node --chown=33:33 /usr/lib /usr/lib
#COPY --from=node --chown=33:33 /usr/local/lib /usr/local/lib
#COPY --from=node --chown=33:33 /usr/local/include /usr/local/include
#COPY --from=node --chown=33:33 /usr/local/bin /usr/local/bin
COPY --from=node --chown=33:33 /app /app
COPY --chown=33:33 ./container/composer.json .
COPY --chown=33:33 ./container/php.ini /usr/local/etc/php/php.ini
COPY --chown=33:33 ./container/default.conf /usr/local/etc/nginx/nginx.conf
COPY --chown=33:33 ./container/zzz-docker.conf /usr/local/etc/php-fpm.d/zzz-docker.conf

RUN set -xe && \
    composer install

CMD ["nginx", "-g", "daemon off;"]