FROM php:apache-bookworm
RUN apt-get update
RUN apt-get install -y vim unzip
RUN apt-get install -y postgresql-server-dev-15 && docker-php-ext-configure pgsql && docker-php-ext-install pgsql
RUN apt-get install -y libfreetype-dev libjpeg62-turbo-dev libpng-dev && docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install -j$(nproc) gd
RUN a2enmod expires && a2enmod rewrite
COPY --chown=33:33 ./src/ /var/www/html/
RUN install -o 33 -g 33 -d/var/www/html/js/ /var/www/html/css/
ADD --chown=33:33 https://code.jquery.com/jquery-3.7.0.min.js /var/www/html/js/jquery.js
ADD --chown=33:33 https://cdn.datatables.net/v/ju/dt-1.13.6/datatables.min.js /var/www/html/js/datatables.js
ADD --chown=33:33 https://cdn.datatables.net/v/ju/dt-1.13.6/datatables.min.css /var/www/html/common/datatables.css
ADD --chown=33:33 https://jqueryui.com/resources/download/jquery-ui-1.13.2.zip /tmp/jquery-ui.zip
RUN unzip -d /tmp/ /tmp/jquery-ui.zip
RUN install -o 33 -g 33 /tmp/jquery-ui-1.13.2/jquery-ui.min.js /var/www/html/js/jquery-ui.js
RUN install -o 33 -g 33 /tmp/jquery-ui-1.13.2/jquery-ui.min.css /var/www/html/css/jquery-ui.css
RUN install -o 33 -g 33 -d /var/www/html/jobs /var/www/html/csvdata
