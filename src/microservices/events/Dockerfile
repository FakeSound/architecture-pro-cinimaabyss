FROM composer:latest AS builder

WORKDIR /app

COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

FROM php:8.2-alpine

WORKDIR /app

# Копируем зависимости из builder
COPY --from=builder /app/vendor ./vendor

RUN apk add --no-cache librdkafka-dev \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka \
    && apk del .build-deps

# Копируем код
COPY index.php ./

EXPOSE 8082

CMD ["php", "-S", "0.0.0.0:8082", "index.php"]