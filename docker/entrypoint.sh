#!/bin/sh
set -eu

if [ "${APP_ENV:-}" = "production" ]; then
    if [ "${APP_DEBUG:-true}" != "false" ] && [ "${APP_DEBUG:-true}" != "0" ]; then
        echo "APP_DEBUG must be false in production." >&2
        exit 1
    fi

    if [ -z "${APP_KEY:-}" ]; then
        echo "APP_KEY must be set in production." >&2
        exit 1
    fi
fi

php artisan config:cache
php artisan view:cache

exec "$@"
