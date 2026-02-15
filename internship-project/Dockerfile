# Use PHP with Apache
FROM php:8.1-apache
RUN apt-get update && apt-get install -y ca-certificates openssl && update-ca-certificates
# 1. Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libssl-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions (mysqli, pdo, pdo_mysql, and zip for composer)
RUN docker-php-ext-install mysqli pdo pdo_mysql zip

# 3. Install & Enable PECL extensions (MongoDB & Redis)
RUN pecl install mongodb redis \
    && docker-php-ext-enable mongodb redis

# 4. Install Composer from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 6. Set working directory
WORKDIR /var/www/html

# 7. Copy project files
COPY . /var/www/html/

# 8. Install composer dependencies
# Added --ignore-platform-reqs to prevent "Exit Code 2" during build
RUN if [ -f composer.json ]; then \
    composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs; \
    fi

# 9. Set permissions for Apache
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]

