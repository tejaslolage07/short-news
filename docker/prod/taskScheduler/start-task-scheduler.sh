#!/usr/bin/with-contenv sh
while [ true ]
do
  php /var/www/html/short-news/artisan schedule:run --verbose --no-interaction &
  sleep 60
done