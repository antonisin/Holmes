FROM php:8.1-fpm-alpine AS php

MAINTAINER Maxim Antonisin <maxim.antonisin@gmail.com>

# Cleanup
RUN rm -rf /var/www/html


RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN apk update && apk add icu-dev gnu-libiconv oniguruma-dev gettext-dev libxml2-dev libmcrypt libmcrypt-dev

RUN ls /usr/local/etc/php
#RUN sed -i -E 's#;opcache.max_accelerated_files.*#opcache.max_accelerated_files=20000#' /usr/local/etc/php/php.ini
#RUN sed -i -E 's#;realpath_cache_size.*#realpath_cache_size=4096K#' /usr/local/etc/php/php.ini
#RUN sed -i -E 's#;realpath_cache_ttl.*#realpath_cache_ttl=600#' /usr/local/etc/php/php.ini

RUN docker-php-ext-install mysqli pdo pdo_mysql intl mbstring
RUN apk add --no-cache  gnu-libiconv

WORKDIR /var/www/api

EXPOSE 9000

FROM php AS php_worker

RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
RUN sed -i -E 's#memory_limit.*#memory_limit=900M#' /usr/local/etc/php/php.ini
RUN sed -i -E 's#post_max_size.*#post_max_size=10000M#' /usr/local/etc/php/php.ini
RUN sed -i -E 's#upload_max_filesize.*#upload_max_filesize=10000M#' /usr/local/etc/php/php.ini
RUN sed -i -E 's#max_execution_time.*#max_execution_time=600#' /usr/local/etc/php/php.ini

RUN apk add supervisor
COPY config/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY config/php/supervisord.conf /etc/supervisord.conf

CMD /usr/bin/supervisord -c /etc/supervisord.conf


FROM php as php_dev

# Install xdebug and enable
COPY config/php/xdebug.ini /tmp/xdebug.ini
RUN apk add autoconf automake gawk build-base libmcrypt-dev
RUN pecl install xdebug mcrypt
RUN mv /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini;
RUN docker-php-ext-enable xdebug
RUN touch /var/log/xdebug.log && chmod 666 /var/log/xdebug.log
RUN cp /tmp/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

FROM php as php_prod

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
COPY ./api /var/www/api
RUN mkdir -p /var/www/api/var/log
RUN mkdir -p /var/www/api/var/cache
RUN chmod -R 777 /var/www/api/var/log
RUN chmod -R 777 /var/www/api/var/cache

RUN composer config --global gitlab-token.github.com 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
#RUN composer install
RUN composer update

COPY config/php/entrypoint.sh /root/entrypoint.sh

ENTRYPOINT ["/bin/sh", "/root/entrypoint.sh"]
