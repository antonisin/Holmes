FROM php:8.0-fpm-alpine

MAINTAINER Maxim Antonisin <maxim.antonisin@gmail.com>

ARG XDEBUG

# Cleanup
RUN rm -rf /var/www/html

#COPY ./www/api /var/www/api

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apk update && apk add icu-dev
RUN docker-php-ext-install mysqli pdo pdo_mysql intl

# Install xdebug and enable
RUN apk add autoconf automake gawk build-base libmcrypt-dev
RUN pecl install xdebug mcrypt
RUN if [ "${XDEBUG}" = 'true' ] ; then mv /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini; else mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini ; fi
RUN if [ "${XDEBUG}" = 'true' ] ; then docker-php-ext-enable xdebug ; fi
RUN if [ "${XDEBUG}" = 'true' ] ; then touch /var/log/xdebug.log && chmod 666 /var/log/xdebug.log ; fi

RUN sed -i -E 's#;opcache.max_accelerated_files.*#opcache.max_accelerated_files=20000#' /usr/local/etc/php/php.ini
RUN sed -i -E 's#;realpath_cache_size.*#realpath_cache_size=4096K#' /usr/local/etc/php/php.ini
RUN sed -i -E 's#;realpath_cache_ttl.*#realpath_cache_ttl=600#' /usr/local/etc/php/php.ini

WORKDIR /var/www/api

#RUN composer install -q ; exit 0
#RUN php bin/console a:i
#RUN chmod -R 777 /var/www/api/var/cache
#RUN chmod -R 777 /var/www/api/var/log

EXPOSE 9000


