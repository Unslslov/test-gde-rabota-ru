# PHP 8.1 FPM
FROM php:8.2-fpm

# Устанавливаем системные зависимости
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    default-mysql-client \
    zip \
    unzip \
    git \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Настраиваем GD extension
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Устанавливаем расширения PHP
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    mbstring \
    exif \
    pcntl \
    bcmath \
    intl \
    gd \
    zip \
    opcache \
    xml

# Включаем расширения (на всякий случай)
RUN docker-php-ext-enable \
    pdo_mysql \
    mysqli \
    opcache

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Настраиваем рабочую директорию
WORKDIR /var/www

# Копируем код приложения
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Устанавливаем правильные права
RUN chown -R www-data:www-data /var/www/storage
RUN chown -R www-data:www-data /var/www/bootstrap/cache

CMD ["php-fpm"]

EXPOSE 9000
