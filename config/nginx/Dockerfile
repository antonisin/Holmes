FROM nginx:1.20.1-alpine

MAINTAINER Maxim Antonisin <maxim.antonisin@gmail.com>

ARG API_DOMAIN

# Cleanup
RUN rm -rf /var/www/html
RUN rm -f /etc/nginx/conf.d/default.conf
RUN mkdir /etc/nginx/sites-enabled
RUN mkdir /etc/nginx/ssl

COPY nginx.conf /etc/nginx
COPY api.conf /etc/nginx/sites-enabled
RUN sed -i -e "s/API_DOMAIN/$API_DOMAIN/" /etc/nginx/sites-enabled/api.conf

COPY isis.crt /etc/nginx/ssl
COPY isis.key /etc/nginx/ssl

RUN echo "upstream php-upstream { server php:9000; }" >> /etc/nginx/conf.d/upstream.conf

EXPOSE 80 443

