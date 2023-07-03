FROM php:8.2.6-fpm

COPY . /var/www/html/short-news
WORKDIR /var/www/html/short-news


# Install dependencies
RUN apt-get update

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


RUN cp .env.production .env \
  && composer install --no-dev --optimize-autoloader \
  && chgrp -R www-data /var/www/html/short-news \
  && chmod -R 775 /var/www/html/short-news/storage \
  && chmod -R 775 /var/www/html/short-news/bootstrap/cache
