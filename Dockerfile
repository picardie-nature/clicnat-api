FROM ndamiens/nginx-php:latest

RUN mkdir /etc/baseobs
COPY composer.json composer.lock ./
RUN composer install -o
COPY www /opt/app/www
COPY src /opt/app/src
