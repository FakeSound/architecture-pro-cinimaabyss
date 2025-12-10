FROM composer:latest AS builder

WORKDIR /app

COPY composer.json ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

FROM php:8.2-alpine

WORKDIR /app

# Копируем зависимости из builder
COPY --from=builder /app/vendor ./vendor

# Копируем код
COPY index.php ./

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "index.php"]