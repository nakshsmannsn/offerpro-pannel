# Official PHP + Apache image
FROM php:8.2-apache

# Install extra tools if needed
RUN apt-get update && apt-get install -y \
    curl unzip libzip-dev \
    && docker-php-ext-install zip

# Copy project files into container
COPY . /var/www/html/

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]