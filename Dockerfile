# LogicsGrid CMS (Laravel 12 + Filament 4) for Dokploy.
# Serves on port 80. Needs a MySQL service and the env vars listed in CMS-GUIDE.md.
# Mount a persistent volume at /var/www/html/storage/app/public so uploads survive redeploys.
FROM php:8.3-apache

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql intl zip gd exif bcmath opcache \
    && a2enmod rewrite headers \
    && sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf "upload_max_filesize=20M\npost_max_size=25M\nmemory_limit=256M\n" > "$PHP_INI_DIR/conf.d/uploads.ini"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first so this layer is cached between deploys.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .

# Build-time env: no database is reachable here, so keep caches in memory.
RUN CACHE_STORE=array SESSION_DRIVER=array DB_CONNECTION=sqlite \
    composer dump-autoload --no-dev --optimize --no-interaction \
    && CACHE_STORE=array DB_CONNECTION=sqlite php artisan filament:assets \
    && mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache public

COPY docker/entrypoint.sh /usr/local/bin/cms-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/cms-entrypoint && chmod +x /usr/local/bin/cms-entrypoint

EXPOSE 80
ENTRYPOINT ["cms-entrypoint"]
CMD ["apache2-foreground"]
