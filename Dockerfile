# ---------- Base ----------
FROM php:8.2-apache

# System packages & PHP extensions (PostgreSQL)
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libonig-dev libzip-dev \
 && docker-php-ext-install pdo pdo_pgsql \
 && a2enmod rewrite headers

# ---------- App dir ----------
WORKDIR /var/www/html

# Copy composer files first to leverage build cache
COPY composer.json composer.lock ./

# Install Composer (avoid docker mirror issues)
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
 && php composer-setup.php --install-dir=/usr/bin --filename=composer \
 && php -r "unlink('composer-setup.php');"

ENV COMPOSER_MEMORY_LIMIT=-1 COMPOSER_ALLOW_SUPERUSER=1

# Install PHP deps WITHOUT scripts (artisan not copied yet)
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts -o

# Now copy the rest of source (adds artisan, app code, public/, etc.)
COPY . .

# Generate autoload & discover packages (now artisan exists)
RUN composer dump-autoload -o \
 && php artisan package:discover --ansi || true

# Storage & permissions
RUN mkdir -p storage bootstrap/cache \
 && chown -R www-data:www-data /var/www/html \
 && chmod -R 775 storage bootstrap/cache \
 && php artisan storage:link || true

# ---------- Apache vhost (8080) ----------
RUN cat > /etc/apache2/sites-available/laravel.conf <<'EOF'
<VirtualHost *:8080>
    ServerName localhost
    DocumentRoot /var/www/html/public

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /proc/self/fd/2
    CustomLog /proc/self/fd/1 combined
</VirtualHost>
EOF

RUN a2dissite 000-default.conf \
 && a2ensite laravel.conf \
 && sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf

# Clear caches safely (do not fail build if env missing)
RUN php artisan config:clear || true \
 && php artisan route:clear || true \
 && php artisan view:clear || true

EXPOSE 8080
CMD ["apache2-foreground"]
