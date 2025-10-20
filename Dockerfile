FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libzip-dev \
 && docker-php-ext-install pdo pdo_pgsql \
 && a2enmod rewrite headers

WORKDIR /var/www/html

# 1) Copy composer files để cache layer
COPY composer.json composer.lock ./

# 2) Cài Composer (trực tiếp, né mirror)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/bin --filename=composer \
 && php -r "unlink('composer-setup.php');"

ENV COMPOSER_MEMORY_LIMIT=-1 COMPOSER_ALLOW_SUPERUSER=1

# 3) Cài dependencies **không chạy scripts** (chưa có artisan)
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts -o

# 4) Copy phần còn lại của source (lúc này mới có file artisan)
COPY . .

# 5) Chạy lại autoload + package:discover
RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true

# 6) Quyền + symlink
RUN chown -R www-data:www-data /var/www/html \
 && mkdir -p storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache \
 && php artisan storage:link || true

# 7) Apache vhost 8080
RUN printf "%s\n" \
 "<VirtualHost *:8080>\nServerName localhost\nDocumentRoot /var/www/html/public\n\
 <Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>\n\
 ErrorLog /proc/self/fd/2\nCustomLog /proc/self/fd/1 combined\n</VirtualHost>" \
 > /etc/apache2/sites-available/laravel.conf \
 && a2dissite 000-default.conf \
 && a2ensite laravel.conf \
 && sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

# 8) Clear caches (an toàn)
RUN php artisan config:clear || true \
 && php artisan route:clear || true \
 && php artisan view:clear || true

EXPOSE 8080
CMD ["apache2-foreground"]
