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
    gettext \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_mysql \
        zip \
        intl \
        gd \
        opcache

# Nginx config template — ${PORT} is substituted at container start by entrypoint.sh
COPY docker/nginx/default.conf.template /etc/nginx/http.d/default.conf.template

# Supervisord config (runs nginx + php-fpm together)
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Entrypoint: writes the live nginx config from the template then starts supervisord
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Composer (cached layer — only re-runs when composer files change)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install deps first (maximise Docker cache on rebuilds)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy the rest of the project
COPY . .

# Create var directory (excluded by .dockerignore) + finish autoloader
RUN mkdir -p var/cache var/log \
    && composer dump-autoload --optimize \
    && php bin/console cache:clear --no-warmup 2>/dev/null || true

# Permissions — Alpine uses www-data (82:82)
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/public \
    && mkdir -p /run/nginx

# Railway overrides PORT at runtime; default 80 keeps local docker-compose working.
EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]