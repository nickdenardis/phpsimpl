FROM php:8.2-cli

# Install mysqli extension (modern replacement for mysql_*)
RUN docker-php-ext-install mysqli

# Install additional extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install Xdebug for coverage (optional)
RUN pecl install xdebug && docker-php-ext-enable xdebug

WORKDIR /app

CMD ["php", "-v"]
