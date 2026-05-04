FROM php:8.2-apache

RUN apt-get update && apt-get install -y apt-transport-https ca-certificates \
    && sed -i 's|http://deb.debian.org|https://deb.debian.org|g' /etc/apt/sources.list.d/debian.sources || true

RUN apt-get update && apt-get install -y \
    curl git unzip libpq-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_pgsql pgsql

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && npm install -g npm@9

RUN a2enmod rewrite \
    && sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

RUN mkdir -p /var/www/html/assets/libs \
    && curl -L https://github.com/dompdf/dompdf/archive/refs/heads/master.zip -o /tmp/dompdf.zip \
    && unzip /tmp/dompdf.zip -d /var/www/html/assets/libs \
    && mv /var/www/html/assets/libs/dompdf-* /var/www/html/assets/libs/dompdf \
    && rm /tmp/dompdf.zip

WORKDIR /var/www/html
COPY . /var/www/html
EXPOSE 80
CMD ["apache2-foreground"]
