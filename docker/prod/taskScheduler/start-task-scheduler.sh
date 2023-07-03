#!/usr/bin/with-contenv sh
while [ true ]
do
  echo "Running scheduler"
  php /var/www/html/short-news/artisan schedule:run --verbose --no-interaction &
  sleep 60
done