FROM webcenter/activemq:5.14.3 AS activemq
COPY docker/activemq.xml conf/activemq.xml
HEALTHCHECK CMD supervisorctl status


FROM rabbitmq:3.7.8-management-alpine AS rabbitmq
RUN rabbitmq-plugins enable --offline rabbitmq_stomp
COPY docker/rabbitmq.conf docker/rabbitmq.json /etc/rabbitmq/
HEALTHCHECK CMD rabbitmqctl status


FROM composer:1.8.0 AS composer
COPY php/composer.json php/composer.lock ./
RUN composer --no-interaction install --classmap-authoritative --ignore-platform-reqs --no-suggest --prefer-dist


FROM php:7.2.13-cli-alpine3.8 AS app
WORKDIR /app/
RUN apk add --no-cache bash
COPY --from=composer /app/vendor/ vendor/
COPY php .
CMD php /app/consume.php
