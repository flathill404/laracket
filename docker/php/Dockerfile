FROM php:8.4-cli

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
  libzip-dev \
  zip \
  unzip \
  git \
  curl \
  && docker-php-ext-install pdo_mysql zip \
  && pecl install redis \
  && docker-php-ext-enable redis \
  && pecl install pcov \
  && docker-php-ext-enable pcov

RUN git config --global --add safe.directory /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

CMD ["php", "artisan", "serve", "--host=0.0.0.0"]
