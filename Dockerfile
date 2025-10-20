# PHP + Apache
FROM php:8.2-apache

# 1) Extensions & tools
RUN apt-get update && apt-get install -y \
    libpq-dev git unzip curl \
 && docker-php-ext-install pdo pdo_pgsql \
 && a2enmod rewrite

# 2) Composer
RUN curl -sS https://getcomposer.org/installer \
 | php -- --install-dir=/usr/local/bin --filename=composer

# 3) Code
WORKDIR /var/www/html
COPY . .

# 4) DocumentRoot -> public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
# chỉnh đúng 2 vhost đang dùng
RUN sed -ri -e 's!DocumentRoot .*!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/default-ssl.conf \
 && sed -ri -e 's!<Directory /var/www/>!<Directory ${APACHE_DOCUMENT_ROOT}/>!g' /etc/apache2/apache2.conf

# 4b) Tạo file cấu hình riêng cho Laravel để cấp quyền truy cập & .htaccess
RUN printf '<Directory "${APACHE_DOCUMENT_ROOT}">\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
 && a2enconf laravel

# 5) Dependencies & optimize
RUN composer install --no-dev --optimize-autoloader \
 && php artisan storage:link || true

# 6) Cache (không fail nếu thiếu env)
RUN php -r "file_exists('.env') || copy('.env.example', '.env');" \
 && php artisan config:cache || true \
 && php artisan route:cache || true

# 7) Render port động
ENV PORT=8080
EXPOSE 8080
RUN sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf

CMD ["apache2-foreground"]
