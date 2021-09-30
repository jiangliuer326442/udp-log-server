FROM php:7.2

RUN docker-php-ext-install -j$(nproc) sockets

RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && rm -rf /usr/local/etc/php/php.ini-development

WORKDIR /var/www/html

COPY . /var/www/html

RUN mkdir -p /app/data/udp_server/

EXPOSE 8010/udp

VOLUME /app/data/udp_server

CMD [ "php", "udp_log_server.php" ]