FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql \
 && a2enmod rewrite \
 && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY . /var/www/html/
COPY docker-start.sh /usr/local/bin/docker-start.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-start.sh \
 && chmod +x /usr/local/bin/docker-start.sh \
 && chown -R www-data:www-data /var/www/html

# Render sends traffic to $PORT (default 10000)
ENV PORT=10000
EXPOSE 10000
CMD ["docker-start.sh"]
