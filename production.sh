cd /app
php artisan migrate --force
chown -R www-data:www-data storage/
chown -R www-data:www-data public/
exec /usr/bin/supervisord -n "$@"
