FROM nginx:1.24.0-alpine



COPY . /var/www/html/short-news
COPY ./docker/prod/nginx/nginx.conf /etc/nginx/nginx.conf
WORKDIR /var/www/html/short-news



RUN chgrp -R www-data /var/www/html/short-news \
    && chmod -R 775 /var/www/html/short-news/storage \
    && chmod -R 775 /var/www/html/short-news/bootstrap/cache