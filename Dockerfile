FROM php:8.4-apache

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    libicu-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    fileinfo \
    intl \
    opcache

# APCu
RUN pecl install apcu \
 && docker-php-ext-enable apcu

# Apache modules
RUN a2enmod rewrite headers expires deflate

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHP config
COPY .docker/php.ini /usr/local/etc/php/conf.d/99-app.ini

# Apache config
COPY .docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Application
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction
COPY . .

# Permissions
RUN mkdir -p App/Storage/Logs App/Storage/Cache Public/upload \
 && chown -R www-data:www-data App/Storage Public/upload \
 && chmod -R 775 App/Storage Public/upload

# Runtime
EXPOSE 80
CMD ["apache2-foreground"]
