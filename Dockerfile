FROM composer:2 AS dependencies

WORKDIR /var/www/html

COPY . .

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

FROM node:22-alpine AS assets

WORKDIR /var/www/html/frontend

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci

COPY frontend/src ./src
COPY frontend/next.config.ts frontend/tsconfig.json frontend/eslint.config.mjs ./
RUN npm run build

FROM php:8.5-fpm-alpine AS app

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install pdo_mysql \
    && apk del .build-deps

WORKDIR /var/www/html

COPY --from=dependencies --chown=www-data:www-data /var/www/html /var/www/html
COPY docker/php.ini /usr/local/etc/php/conf.d/production.ini
COPY docker/entrypoint.sh /usr/local/bin/app-entrypoint

RUN mkdir -p storage/framework/cache/data storage/framework/sessions \
    storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

ENTRYPOINT ["sh", "/usr/local/bin/app-entrypoint"]
CMD ["php-fpm", "-F"]

FROM node:22-alpine AS frontend

WORKDIR /app

ENV NODE_ENV=production

COPY --from=assets /var/www/html/frontend/.next/standalone ./
COPY --from=assets /var/www/html/frontend/.next/static ./.next/static
COPY --from=app /var/www/html/public ./public

USER node

EXPOSE 3000
CMD ["node", "server.js"]

FROM nginx:1.28-alpine AS web

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public

EXPOSE 8080
