#!/bin/sh
set -eu

export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"
export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export SESSION_SECURE_COOKIE="${SESSION_SECURE_COOKIE:-true}"
export APP_URL="${APP_URL:-https://${RENDER_EXTERNAL_HOSTNAME:-localhost}}"
export SANCTUM_STATEFUL_DOMAINS="${RENDER_EXTERNAL_HOSTNAME:-localhost}"

if [ "$APP_ENV" = "production" ]; then
    if [ "$APP_DEBUG" != "false" ] && [ "$APP_DEBUG" != "0" ]; then
        echo "APP_DEBUG must be false in production." >&2
        exit 1
    fi

    if [ -z "${APP_KEY:-}" ]; then
        echo "APP_KEY must be set in production." >&2
        exit 1
    fi

    case "$APP_KEY" in
        base64:*) ;;
        *)
            APP_KEY="base64:$(printf '%s' "$APP_KEY" | php -r 'echo base64_encode(hash("sha256", stream_get_contents(STDIN), true));')"
            export APP_KEY
            ;;
    esac
fi

php artisan migrate --force
php artisan config:cache
php artisan view:cache

php-fpm -D
cd /var/www/html/frontend
su-exec www-data env HOSTNAME=127.0.0.1 PORT=3000 node server.js &

exec nginx -g "daemon off;"
