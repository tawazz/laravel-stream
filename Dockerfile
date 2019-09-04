FROM ubuntu:18.04
RUN apt-get update && apt-get install software-properties-common -y
RUN add-apt-repository ppa:nginx/stable
RUN add-apt-repository ppa:ondrej/php
RUN apt-get update
ENV TZ=Australia/Perth
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get install php7.3-fpm php7.3-common php7.3-mysql php7.3-xml php7.3-xmlrpc \
    php7.3-curl  php7.3-gd php7.3-imagick php7.3-cli php7.3-dev php7.3-imap php7.3-mbstring \
    php7.3-opcache php7.3-soap php7.3-zip php7.3-intl php7.3-bcmath composer nginx ffmpeg -y
RUN rm /etc/nginx/sites-enabled/default
RUN mkdir -p /app
WORKDIR /app
RUN apt-get install supervisor -y
COPY . .
COPY .docker/site.conf /etc/nginx/sites-enabled/app.conf
COPY .docker/supervisor.conf /etc/supervisor/conf.d/start.conf
COPY .docker/php.ini /etc/php/7.3/fpm/php.ini
RUN chown -R www-data:www-data .
RUN mkdir -p /var/run/php
EXPOSE 80
EXPOSE 443
EXPOSE 9001

CMD ["/usr/bin/supervisord" ,"-n"]
