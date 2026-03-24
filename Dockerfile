FROM php:8.3-apache-bookworm

# Installation dépendances système (single layer, clean cache)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo \
        pdo_mysql \
        zip \
        intl \
        gd \
        opcache \
    && rm -rf /var/lib/apt/lists/*

# Apache config
RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Symfony-optimised Apache VirtualHost (no .htaccess needed)
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot ${APACHE_DOCUMENT_ROOT}\n\
    <Directory ${APACHE_DOCUMENT_ROOT}>\n\
        AllowOverride All\n\
        Require all granted\n\
        FallbackResource /index.php\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

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

# Permissions
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/public

EXPOSE 80