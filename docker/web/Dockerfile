FROM php:apache
RUN apt-get update
RUN apt-get install -y postgresql-server-dev-15 && docker-php-ext-configure pgsql && docker-php-ext-install pgsql
RUN apt-get install -y libfreetype-dev libjpeg62-turbo-dev libpng-dev && docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd
RUN a2enmod expires && a2enmod rewrite
COPY --chown=33:33 ./src/ /var/www/html/