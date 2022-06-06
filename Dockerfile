FROM php:8.1

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
