FROM php:alpine
RUN apk update
RUN apk add postgresql15-dev && docker-php-ext-configure pgsql && docker-php-ext-install pgsql
RUN apk add freetype-dev libjpeg-turbo-dev libpng-dev && docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd
RUN a2enmod expires && a2enmod rewrite
COPY --chown=apache:apache ./src/ /var/www/html/
RUN mkdir /var/www/html/jobs /var/www/html/csvdata && chown 33:33 /var/www/html/jobs /var/www/html/csvdata