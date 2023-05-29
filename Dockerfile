FROM composer:latest AS base

WORKDIR /var/www

# Install composer dependencies
COPY composer.json composer.lock ./
RUN composer install --no-autoloader

FROM node:18 AS assets

WORKDIR /var/www

# Install npm dependencies
COPY package.json package-lock.json ./
RUN npm install

# build resources
# TODO: can this be done in some other way that improves caching?
COPY resources/ ./resources
COPY vite.config.js ./
COPY --from=base /var/www/vendor ./vendor

RUN npm run build

FROM base AS final

# Copy the artisan command
COPY artisan ./

# Copy bootstrap code
COPY bootstrap/ ./bootstrap/

# Copy application configuration files
COPY ./config ./config

# Copy public files
COPY --from=assets /var/www/public ./public
COPY --from=assets /var/www/resources ./resources

# copy application data
COPY routes/ ./routes/
COPY app/ ./app/
COPY routes/ ./routes/

# Create composer autoloader
RUN composer dumpautoload

# Copy environment variables
COPY .env .

# Run composer scripts
RUN php artisan storage:link
RUN php artisan key:generate --force
RUN php artisan optimize

# run the application
CMD ["php", "artisan", "serve"]