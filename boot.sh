cd /app
npm install
npm run production
composer install --ignore-platform-reqs --no-interaction --prefer-dist --optimize-autoloader --no-dev
php artisan migrate --force
chown -R www-data:www-data storage/
chown -R www-data:www-data public/

exec /usr/bin/supervisord -n "$@"
