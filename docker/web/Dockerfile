FROM php:apache
RUN apt-get update && apt-get install -y postgresql-server-dev-15 && docker-php-ext-configure pgsql && docker-php-ext-install pgsql
COPY --chown www-data:www-data ../src/ /var/www/html/