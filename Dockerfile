FROM php:8.4-fpm-alpine

# Install system deps (Alpine packages) + nginx + supervisord
RUN apk add --no-cache \
    git \
    unzip \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    mysql-client \
    nginx \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_mysql \
        zip \
        intl \
        gd \
        opcache

# Nginx config
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Supervisord config (runs nginx + php-fpm together)
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Composer (cached layer — only re-runs when composer files change)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install deps first (maximise Docker cache on rebuilds)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy the rest of the project
COPY . .

# Force prod environment for all build-time console commands
ENV APP_ENV=prod APP_DEBUG=0

# Create var directory (excluded by .dockerignore) + finish autoloader
RUN mkdir -p var/cache var/log var/tailwind \
    && composer dump-autoload --optimize \
    && php bin/console cache:clear --no-warmup --env=prod

# Build Tailwind CSS then compile all assets to public/assets/ for static serving
RUN php bin/console tailwind:build --minify --env=prod \
    && php bin/console asset-map:compile --env=prod

# Permissions — Alpine uses www-data (82:82)
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/public \
    && mkdir -p /run/nginx

EXPOSE 80

CMD ["sh", "-c", "chown -R www-data:www-data /var/www/html/var && exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"]