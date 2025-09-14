FROM php:8.4-fpm

ARG USER
ARG UID
ARG API_PORT=9000

# Set working directory
WORKDIR /var/www

# Install system dependencies and PHP extensions
# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \    
    locales \
    zip \
    vim \
    unzip \
    git \
    curl \
    supervisor \
    libzip-dev && \
    pecl install xdebug && docker-php-ext-enable xdebug && \
    docker-php-ext-install pdo pdo_mysql && \
    docker-php-ext-install gd
    
# Copy the application code into the container
COPY . /var/www

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer install --prefer-dist --no-dev --optimize-autoloader

# Clear cache
RUN apt-get purge -y git build-essential \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/*

RUN useradd -G www-data,root -u $UID -d /home/$USER $USER
RUN mkdir -p /home/$USER/.composer && \
    chown -R $USER:$USER /home/$USER

# Allow passwordless sudo for www user
RUN echo "$USER ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Change current user to www
USER $USER

# Expose port 9000 and start php-fpm server
EXPOSE $API_PORT