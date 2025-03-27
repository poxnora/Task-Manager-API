# Use official PHP 8.1 image with FPM
FROM php:8.1-fpm

# Set working directory
WORKDIR /app

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql sockets

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install RoadRunner
COPY --from=ghcr.io/roadrunner-server/roadrunner:2.12.0 /usr/bin/rr /usr/bin/rr

# Copy application code
COPY . .

# Install PHP dependencies including PSR-7 and RoadRunner requirements
RUN rm -f composer.lock \
    && composer require nyholm/psr7:1.8.1 symfony/psr-http-message-bridge:^2.3 spiral/roadrunner:^2.0 --no-scripts \
    && composer install --optimize-autoloader --no-scripts

# Expose port 8080
EXPOSE 8080

# Start RoadRunner
CMD ["/app/wait-for-postgres.sh", "&&", "php", "bin/console", "doctrine:migrations:migrate", "--no-interaction", "&&", "rr", "serve", "-c", ".rr.yaml"]