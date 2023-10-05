FROM composer:2 AS dev-dependencies

COPY . .

RUN composer install --no-interaction

FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install

COPY ./resources/ ./resources
COPY ./vite.config.ts ./tsconfig.json ./tsconfig.app.json ./tsconfig.node.json ./tailwind.config.js ./postcss.config.js ./
COPY --from=dev-dependencies /app/vendor ./vendor

RUN npm run build

FROM composer:2 AS dependencies

COPY . .

RUN composer install --no-interaction --optimize-autoloader --no-dev --prefer-dist

FROM trafex/php-nginx:3.4.0 AS production

WORKDIR /var/www/html

USER root

RUN apk add --no-cache php82-pdo_pgsql php82-pgsql php82-pecl-redis php82-iconv

USER nobody

COPY ./etc/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./etc/php/php.ini ${PHP_INI_DIR}/conf.d/php.ini

COPY --chown=nobody ./.env.prod ./.env

COPY --chown=nobody --from=dependencies /app/vendor ./vendor
COPY --chown=nobody --from=assets /app/public ./public
COPY --chown=nobody ./app ./app
COPY --chown=nobody ./artisan ./artisan
RUN mkdir -p ./bootstrap/cache
COPY --chown=nobody ./bootstrap/app.php ./bootstrap/app.php
COPY --chown=nobody ./config ./config
COPY --chown=nobody ./composer.json ./composer.lock ./
COPY --chown=nobody ./database ./database
COPY --chown=nobody ./public ./public
COPY --chown=nobody ./resources ./resources
COPY --chown=nobody ./routes ./routes
COPY --chown=nobody ./storage ./storage

RUN php artisan key:generate --force
RUN php artisan storage:link
RUN php artisan config:cache
RUN php artisan event:cache
RUN php artisan route:cache
RUN php artisan view:cache
RUN php artisan optimize
