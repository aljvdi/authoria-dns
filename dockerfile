# Start from the official PHP 8.2 image with Apache
FROM php:8.2-apache

# Set the working directory in the Docker image
WORKDIR /var/www/html

# Download and install Sqlite3 & git
RUN apt-get update && apt-get upgrade -y && apt-get install -y sqlite3 libsqlite3-dev && apt-get install -y git

# Install the pdo_sqlite extension
RUN docker-php-ext-install pdo_sqlite && docker-php-ext-enable pdo_sqlite

# Copy the application code to the Docker image
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Run Composer install to install the PHP dependencies
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# run db/init.php
RUN php db/init.php && rm -rf db/init.php

# The problem, as it turns out, is that the PDO SQLite driver requires that if you are going to do a write operation (INSERT,UPDATE,DELETE,DROP, etc), then the folder the database resides in must have write permissions, as well as the actual database file.
RUN chmod -R 777 db

# Expose port 80 for HTTP (In our use case, we are using port 80 and then we secure it with a reverse proxy)
# If you need to use HTTPS, you can expose port 443 as well, However, remember to update the Apache configuration to use SSL
EXPOSE 80

# Set the necessary environment variables
ENV APACHE_DOCUMENT_ROOT /var/www/html/
# ENV APP_MODE PROD || DEV

# Update the Apache configuration
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable the Apache mod_rewrite module
RUN a2enmod rewrite

# Define the command to run the application
CMD ["apache2-foreground"]