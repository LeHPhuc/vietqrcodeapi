# Dockerfile
FROM php:8.2-apache

# Cài extension cần cho Laravel + Postgres
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libzip-dev \
 && docker-php-ext-install pdo pdo_pgsql \
 && a2enmod rewrite headers

# Đưa app vào /var/www/html
WORKDIR /var/www/html
COPY . /var/www/html

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress

# Quyền + symlink storage
RUN chown -R www-data:www-data /var/www/html \
 && php artisan storage:link || true

# Apache vhost: bật rewrite cho public/
RUN printf "%s\n" \
    "<VirtualHost *:8080>" \
    "  ServerName localhost" \
    "  DocumentRoot /var/www/html/public" \
    "  <Directory /var/www/html/public>" \
    "    AllowOverride All" \
    "    Require all granted" \
    "  </Directory>" \
    "  ErrorLog /proc/self/fd/2" \
    "  CustomLog /proc/self/fd/1 combined" \
    "</VirtualHost>" \
    > /etc/apache2/sites-available/laravel.conf \
 && a2dissite 000-default.conf \
 && a2ensite laravel.conf

# Apache lắng nghe 8080 (Render expect)
EXPOSE 8080
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

# Cache config/route/view để nhanh hơn (không fail build nếu env thiếu)
RUN php artisan config:clear || true \
 && php artisan route:clear || true \
 && php artisan view:clear || true

CMD ["apache2-foreground"]
