FROM php:8.2-apache

# Install PHP extensions for MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache modules
RUN a2enmod rewrite

# Copy project files into the web root
COPY . /var/www/html/

# Ensure a default landing page exists
RUN printf "DirectoryIndex index.php login.php\n" > /etc/apache2/conf-available/dirindex.conf && a2enconf dirindex

EXPOSE 80

CMD ["apache2-foreground"]
