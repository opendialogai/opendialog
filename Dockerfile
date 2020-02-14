FROM php:7.3-fpm

# Install dependencies
RUN curl -sL https://deb.nodesource.com/setup_10.x | bash - && \
    apt-get update && apt-get install -y \
    nodejs \
    build-essential \
    mariadb-client \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    nginx && \
    npm install -g npm && \
    /etc/init.d/nginx start

# Install extensions
RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/
RUN docker-php-ext-install gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY --chown=www-data:www-data . /var/www

# Copy the nginx conf file
RUN rm /etc/nginx/sites-enabled/default
RUN rm /etc/nginx/sites-available/default

COPY --chown=www-data:www-data docker-app.conf /etc/nginx/sites-available

RUN ln -s /etc/nginx/sites-available/docker-app.conf /etc/nginx/sites-enabled/docker-app.conf

RUN chown -R www-data:www-data /var/www

RUN chmod +x ./scripts/set_up_od_docker.sh
RUN ./scripts/set_up_od_docker.sh

RUN chmod -R 755 /var/www/storage

COPY .env.example.docker .env

# Expose port 9000 and start php-fpm server
EXPOSE 9000
EXPOSE 80
EXPOSE 443

RUN ["chmod", "+x", "./scripts/docker-start.sh"]
CMD ["./scripts/docker-start.sh"]
