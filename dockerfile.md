# Use the official PHP image with Apache
FROM php:8.2-apache

# Copy your website files into Apache's web root
COPY . /var/www/html/

# Optional: Enable Apache rewrite (if using .htaccess)
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
