FROM php:8.2-cli

# Cài thư viện hệ thống
RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev libzip-dev zip \
    && docker-php-ext-install pdo pdo_pgsql zip

# Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Cài NodeJS (để build Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Set thư mục làm việc
WORKDIR /var/www

# Copy code
COPY . .

# Cài PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Build Vite
RUN npm install && npm run build

# Cache Laravel
RUN php artisan optimize

# Expose port
EXPOSE 10000

# Run server
CMD php artisan serve --host=0.0.0.0 --port=10000