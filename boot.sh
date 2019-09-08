cd /app
npm install
npm run production
composer install --ignore-platform-reqs --no-interaction --prefer-dist --optimize-autoloader --no-dev
php artisan migrate

exec /usr/bin/supervisord -n "$@"
