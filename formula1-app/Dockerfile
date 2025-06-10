FROM php:8.3-fpm


# Instalar dependencias del sistema

RUN apt-get update && apt-get install -y \

    git \

    curl \

    libpng-dev \

    libonig-dev \

    libxml2-dev \

    zip \

    unzip \

    libzip-dev \

    libicu-dev \

    && apt-get clean \

    && rm -rf /var/lib/apt/lists/*


# Instalar extensiones PHP

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl


# Configurar PHP para Composer

RUN echo "memory_limit=512M" > /usr/local/etc/php/conf.d/memory-limit.ini


# Obtener Composer

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# Establecer directorio de trabajo

WORKDIR /var/www


# Copiar TODO el código fuente primero

COPY . .


# Configurar Composer

ENV COMPOSER_ALLOW_SUPERUSER=1

ENV COMPOSER_MEMORY_LIMIT=-1


# Instalar dependencias PHP (ahora que artisan existe)

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist


# Establecer permisos

RUN chown -R www-data:www-data storage bootstrap/cache \

    && chmod -R 755 /var/www/bootstrap/cache


# Exponer puerto

EXPOSE 9000


CMD ["php-fpm"]