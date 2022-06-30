FROM php:8.1.1-cli-bullseye

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get install -y \
       libzip-dev \
       zip \
    && docker-php-ext-install bcmath zip

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
RUN echo "assert.exception = On" >> "$PHP_INI_DIR/php.ini"

# Install Composer
RUN curl \
        --location \
        --silent \
        --show-error \
        https://github.com/composer/composer/releases/download/2.2.1/composer.phar \
        > \
        /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer
