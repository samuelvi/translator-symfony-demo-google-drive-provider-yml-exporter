FROM php:8.4-cli


# Set working directory
WORKDIR /app

# Install dependencies
RUN apt-get update && apt-get install -y    \
        libfreetype6-dev   \
        libjpeg62-turbo-dev   \
        libpng-dev     \
        libzip-dev   \
        build-essential

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd zip

# Install Xdebug
RUN pecl install xdebug-3.4.2
RUN docker-php-ext-enable xdebug

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN chown www-data:www-data /var/www/html