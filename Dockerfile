# Use official PHP image with Apache
FROM php:8.2-apache

# Enable Apache mod_rewrite (optional)
RUN a2enmod rewrite

# Install curl extension
RUN docker-php-ext-install curl

# Copy project files to container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]