# Dockerfile
FROM php:8.2-apache

# 1) Packages & PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libzip-dev \
 && docker-php-ext-install pdo pdo_pgsql \
 && a2enmod rewrite headers

# 2) Làm việc trong /var/www/html
WORKDIR /var/www/html

# 3) Copy trước composer files để tận dụng layer cache
COPY composer.json composer.lock ./

# 4) Cài Composer trực tiếp (tránh mirror docker composer:2 bị 502)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/bin --filename=composer \
 && php -r "unlink('composer-setup.php');"

# 5) Cài deps (tối ưu I/O)
ENV COMPOSER_MEMORY_LIMIT=-1 COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress -o

# 6) Copy phần còn lại của source
COPY . .

# 7) Quyền & symlink storage (kèm bootstrap/cache)
RUN chown -R www-data:www-data /var/www/html \
 && mkdir -p storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache \
 && php artisan storage:link || true

# 8) Apache vhost tại 8080
RUN printf "%s\n" \
 "<VirtualHost *:8080>\nServerName localhost\nDocumentRoot /var/www/html/public\n\
 <Directory /var/www/html/public>\nAllowOverride All\nRequire all granted\n</Directory>\n\
 ErrorLog /proc/self/fd/2\nCustomLog /proc/self/fd/1 combined\n</VirtualHost>" \
 > /etc/apache2/sites-available/laravel.conf \
 && a2dissite 000-default.conf \
 && a2ensite laravel.conf \
 && sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

# 9) Clear caches (không fail nếu thiếu env)
RUN php artisan config:clear || true \
 && php artisan route:clear || true \
 && php artisan view:clear || true

EXPOSE 8080
CMD ["apache2-foreground"]
