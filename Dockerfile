# PHP + Apache (Debian)
FROM php:8.2-apache

# 1) Cài ext & tools cần thiết
RUN apt-get update && apt-get install -y \
    libpq-dev git unzip curl \
 && docker-php-ext-install pdo pdo_pgsql \
 && a2enmod rewrite

# 2) Cài Composer
RUN curl -sS https://getcomposer.org/installer \
 | php -- --install-dir=/usr/local/bin --filename=composer

# 3) Copy code & set workdir
WORKDIR /var/www/html
COPY . .

# 4) Đặt document root về public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/default-ssl.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}/!g' /etc/apache2/apache2.conf


# 5) Cài dependency & optimize (safe mode cho build lần đầu)
RUN composer install --no-dev --optimize-autoloader \
 && php artisan storage:link || true

# 6) Cache config/route (không fail nếu thiếu env)
RUN php -r "file_exists('.env') || copy('.env.example', '.env');" \
 && php artisan config:cache || true \
 && php artisan route:cache || true

# 7) Render dùng PORT động → đổi Apache lắng nghe PORT
ENV PORT=8080
EXPOSE 8080
RUN sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf

CMD ["apache2-foreground"]
