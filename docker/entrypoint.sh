#!/bin/sh
set -eu

port="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

case "${DB_HOST:-127.0.0.1}" in
    localhost|127.0.0.1|::1)
        echo "ERROR: Set DB_HOST in Render to your external MySQL database host; localhost refers to this container." >&2
        exit 1
        ;;
esac

if [ -e public/storage ] && [ ! -L public/storage ]; then
    if [ -d public/storage ] \
        && [ -z "$(find public/storage -mindepth 1 -maxdepth 1 ! -name .gitkeep -print -quit)" ]; then
        rm -f public/storage/.gitkeep
        rmdir public/storage
    else
        echo "ERROR: public/storage exists and is not Laravel's storage symlink; refusing to remove its contents." >&2
        exit 1
    fi
fi

php artisan storage:link --force
php artisan migrate --force

exec apache2-foreground
