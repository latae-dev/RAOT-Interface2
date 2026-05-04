FROM php:8.2-apache

RUN apt-get update && apt-get install -y apt-transport-https ca-certificates \
    && sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list.d/debian.sources || true

RUN apt-get update && apt-get install -y \
    curl git unzip libpq-dev libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip pdo pdo_pgsql pgsql

RUN a2enmod rewrite \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY composer.json composer.lock* ./
RUN if [ -f composer.lock ]; then \
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
        && composer install --no-dev --no-interaction --prefer-dist --no-progress; \
    fi

COPY . /var/www/html

# Session ต้องเริ่มก่อนหน้าเพจ output HTML — ป้องกัน headers already sent
RUN echo 'auto_prepend_file = /var/www/html/configs/session_bootstrap.php' > /usr/local/etc/php/conf.d/raot-session.ini

EXPOSE 80
CMD ["apache2-foreground"]
