#!/bin/sh

set -eu

if [ "${APP_KEY#base64:}" = "$APP_KEY" ]; then
    APP_KEY="base64:$(php -r 'echo base64_encode(hash("sha256", getenv("APP_KEY"), true));')"
    export APP_KEY
fi

database_path="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
database_created=false

if [ ! -f "$database_path" ]; then
    touch "$database_path"
    database_created=true
fi

php artisan migrate --force

if [ "$database_created" = true ]; then
    php artisan db:seed --force
fi

php artisan config:cache
php artisan view:cache

exec "$@"
