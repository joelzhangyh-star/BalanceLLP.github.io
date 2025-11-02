# Use the official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies (needed for Composer)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Copy composer.json and composer.lock first (for build caching)
COPY composer.json composer.lock* ./

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

# Install PHP dependencies (this installs PHPMailer)
RUN composer install --no-interaction --no-dev --prefer-dist

# Copy the rest of your project files
COPY . .

# Optional: Enable Apache rewrite module (only if you're using .htaccess)
RUN a2enmod rewrite

# Expose port 80 (default for HTTP)
EXPOSE 80

# Start Apache when the container launches
CMD ["apache2-foreground"]
