FROM short-news-php:prod

RUN apt-get update && apt-get install -y supervisor

COPY '/docker/prod/queue/queue.conf' '/etc/supervisord.conf'

ENTRYPOINT ["supervisord", "-c", "/etc/supervisord.conf"]
