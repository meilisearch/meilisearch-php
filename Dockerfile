FROM php:8.2

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory-limit.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
