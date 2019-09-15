cd /app
php artisan migrate --force

exec /usr/bin/supervisord -n "$@"
