#------------
# node
#------------
FROM node:18-alpine AS node

ENV TZ Asia/Tokyo
RUN ln -snf /usr/share/zoneinfo/Asia/Tokyo /etc/localtime && echo 'Asia/Tokyo' > /etc/timezone

VOLUME ["/app"]

COPY --chown=www-data:www-data . /app
COPY --chown=www-data:www-data ./package.json /app/
COPY --chown=www-data:www-data ./webpack.config.js /app/
COPY --chown=www-data:www-data ./gulpfile.js /app/


WORKDIR /app
#RUN set -xe && \
#    usermod -G www-data,staff www-data && \
#    chown -R www-data /app

RUN set -xe && \
    npm install -g npm@10 && \
    npm install && \
    npm start && \
    npm run build


#------------
# php-fpm
#------------
FROM php:7.4-fpm-alpine AS app

#RUN addgroup -S nginx && adduser -S nginx -G nginx

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

VOLUME ["/app"]

WORKDIR /app

COPY --from=node --chown=www-data:www-data /app /app
COPY --from=node /usr/lib /usr/lib
COPY --from=node /usr/local/lib /usr/local/lib
COPY --from=node /usr/local/include /usr/local/include
COPY --from=node /usr/local/bin /usr/local/bin

COPY --chown=www-data:www-data ./composer.json .
COPY ./php.ini /usr/local/etc/php/

#RUN set -xe && \
#    usermod -G www-data,staff www-data && \
#    chown -R www-data:www-data /app

RUN set -xe && \
    composer clearcache && \
    composer install

EXPOSE 9000
CMD ["php-fpm"]