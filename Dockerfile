# WooSpeed Analytics Development Dockerfile
#
# This Dockerfile sets up a complete WordPress development environment
# with WooSpeed Analytics plugin for testing and development.
#
# @package WooSpeed_Analytics
# @since 3.0.0

FROM php:8.1-cli

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    mysql-client \
    nodejs \
    npm \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

# Install PHPUnit for testing
RUN composer global require phpunit/phpunit ^10.5

# Install Playwright for E2E tests
RUN npm install -g @playwright/test && npx playwright install --with-deps

# Copy plugin files
COPY . /var/www/html/wp-content/plugins/woospeed-analytics/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 80
EXPOSE 80

# Set environment variables
ENV WORDPRESS_DB_HOST=db \
    WORDPRESS_DB_NAME=wordpress \
    WORDPRESS_DB_USER=wordpress \
    WORDPRESS_DB_PASSWORD=wordpress \
    WORDPRESS_TABLE_PREFIX=wp_

# Default command
CMD ["php", "-S", "0.0.0.0:80", "-t", "/var/www/html"]
