FROM php:8.4-fpm

LABEL authors="Gonzalez Fernando"

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar solo composer para cache
COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader || true

# Copiar el resto del proyecto
COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache

CMD ["php-fpm"]
