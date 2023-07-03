FROM php:8.2.6-fpm

COPY . /var/www/html/short-news
WORKDIR /var/www/html/short-news


# Install dependencies
RUN apt-get update && apt-get install -y \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  libzip-dev \
  libicu-dev \
  unzip \
  default-mysql-client \
  netbase \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-configure intl \
  && docker-php-ext-install -j$(nproc) bcmath gd intl opcache pcntl pdo pdo_mysql sockets zip \
  && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
  && sed -i "s|;*upload_max_filesize =.*|upload_max_filesize = 50M|i" "$PHP_INI_DIR/php.ini" \
  && sed -i "s|;*post_max_size =.*|post_max_size = 51M|i" "$PHP_INI_DIR/php.ini" \
  && sed -i "s|;*memory_limit =.*|memory_limit = 512M|i" "$PHP_INI_DIR/php.ini" \
  && sed -i "s|;*max_execution_time =.*|max_execution_time = 300|i" "$PHP_INI_DIR/php.ini" \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Clear cache
# RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN cp .env.production .env \
  && composer install --no-dev --optimize-autoloader \
  && chgrp -R www-data /var/www/html/short-news \
  && chmod -R 775 /var/www/html/short-news/storage \
  && chmod -R 775 /var/www/html/short-news/bootstrap/cache
