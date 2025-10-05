#I have no idea how to use docker, so ChatGPT

# Use the official PHP image with Apache
FROM php:8.2-apache

# Copy all your project files into Apache's web root
COPY . /var/www/html/

# Optional: Enable Apache rewrite module (only if you're using .htaccess)
RUN a2enmod rewrite

# Expose port 80 (default for HTTP)
EXPOSE 80