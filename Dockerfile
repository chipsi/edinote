FROM php:8.1-apache
COPY . /var/www
RUN sed -i 's|/var/www/html|/var/www/public|g' /etc/apache2/sites-available/000-default.conf
EXPOSE 80
