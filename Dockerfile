FROM hub.tich.us/tawazz/nginx-php7.3
RUN apt-get update && apt-get install jpegoptim optipng pngquant gifsicle webp -y
RUN mkdir -p /app
WORKDIR /app
COPY . .
COPY .docker/site.conf /etc/nginx/sites-enabled/app.conf
COPY .docker/supervisor.conf /etc/supervisor/conf.d/app.conf
COPY .docker/php.ini /etc/php/7.3/fpm/php.ini
RUN chown -R www-data:www-data .
RUN mkdir -p /var/run/php
EXPOSE 80
EXPOSE 443
EXPOSE 9001
CMD ["/bin/bash", "boot.sh"]
