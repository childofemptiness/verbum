FROM php:8.1-apache

# Установка расширений PHP
RUN docker-php-ext-install pdo_mysql

# Копирование исходного кода проекта в контейнер
COPY app/ /var/www/html/

# Установка прав владельца для директории
RUN chown -R www-data:www-data /var/www/html/

# Установка ServerName
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

EXPOSE 80
CMD ["apache2-foreground"]
