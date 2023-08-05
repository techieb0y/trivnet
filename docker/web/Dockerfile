FROM php:apache
RUN apt-get update && apt-get install -y postgresql-server-dev-15 && docker-php-ext-configure pgsql && docker-php-ext-install pgsql
COPY --chown=33:33 ../src/ /var/www/html/