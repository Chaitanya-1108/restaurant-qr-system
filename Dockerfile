# Use official PHP with Apache
FROM php:8.2-apache

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy all project files into container
COPY . /var/www/html/

# Allow Apache to use .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port (Render will map automatically)
EXPOSE 80
