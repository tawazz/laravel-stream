cd /app
composer install --ignore-platform-reqs --no-interaction --prefer-dist --optimize-autoloader --no-dev
php artisan migrate --force

exec /usr/bin/supervisord -n "$@"
