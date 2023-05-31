FROM ubuntu:22.04
ARG HOST_UID=33

# Install dependencies
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update
RUN apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    git \
    vim \
    ssh \
    gcc \
    g++ \
    make \
    unzip

RUN apt-get install -y --no-install-recommends \
    libpq-dev \
    php8.1-fpm \
    php8.1-mbstring \
    php8.1-xml \
    php8.1-pgsql \
    php8.1-curl \
    php8.1-gd

COPY --from=composer/composer:2-bin /composer /usr/bin/composer

RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y --no-install-recommends nodejs

WORKDIR /var/www

VOLUME [ \
    "/var/www/storage", "/var/www/app", \
    "/var/www/resources", "/var/www/routes" \
    ]

RUN chown www-data /var/www
RUN chmod 700 /var/www
RUN usermod -u $HOST_UID www-data
USER www-data

# Copy project code and install project dependencies
COPY --chown=www-data composer.json composer.lock ./
RUN composer install --no-autoloader

COPY --chown=www-data package.json package-lock.json ./
RUN npm install

COPY --chown=www-data . .

RUN rm .env*
COPY --chown=www-data .env .env

RUN composer dumpautoload

RUN php artisan storage:link

# Copy project configurations
COPY --chown=www-data ./etc/php/php.ini /usr/local/etc/php/conf.d/php.ini

EXPOSE 8000
EXPOSE 5173

# Start command
CMD npm run dev & php artisan serve --host=0.0.0.0 --port=$PORT
