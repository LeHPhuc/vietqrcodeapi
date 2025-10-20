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

# 4) Port động của Render + tạo vhost Laravel
ENV PORT=8080
EXPOSE 8080
RUN sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf

# 4a) Tạo vhost riêng cho Laravel (lắng nghe ${PORT})
RUN printf '<VirtualHost *:%s>\n\
    ServerName _\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/laravel_error.log\n\
    CustomLog ${APACHE_LOG_DIR}/laravel_access.log combined\n\
</VirtualHost>\n' "$PORT" > /etc/apache2/sites-available/laravel.conf \
 && a2dissite 000-default default-ssl || true \
 && a2ensite laravel

# 5) Dependencies & optimize
RUN composer install --no-dev --optimize-autoloader \
 && php artisan storage:link || true

# 6) Cache (không fail nếu thiếu env)
RUN php -r "file_exists('.env') || copy('.env.example', '.env');" \
 && php artisan config:cache || true \
 && php artisan route:cache || true

CMD ["apache2-foreground"]
