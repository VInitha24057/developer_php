FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd mysqli \
    && pecl install mongodb redis \
    && docker-php-ext-enable mongodb redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

WORKDIR /var/www/html

COPY . .

RUN { \
    echo '#!/bin/bash'; \
    echo 'sed -i "s/localhost/mysql/g" /var/www/html/php/db.php'; \
    echo 'sed -i "s|mongodb://127.0.0.1:27017|mongodb://mongo:27017|g" /var/www/html/php/mongo.php'; \
    echo 'sed -i "s/127.0.0.1/redis/g" /var/www/html/php/redis.php'; \
    echo 'exec "$@"'; \
} > /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]

EXPOSE 80