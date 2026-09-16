#!/usr/bin/env sh
set -e

# Configure port for Nginx
export PORT=${PORT:-8080}
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

# Ensure storage directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Fallback environment variables if not supplied
export APP_KEY="${APP_KEY:-base64:l7E+6brnMSvP9dnx6QOumHLSLB1auRcsSpKff3HGtLo=}"
export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"

# Clear any stale cached configuration
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Run database migrations if database host is configured
if [ -n "$DB_HOST" ] || [ -n "$MYSQLHOST" ] || [ -n "$DATABASE_URL" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || echo "Database not ready yet or migration failed."
fi

# Cache routes and views
if [ "$APP_ENV" = "production" ]; then
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "Starting Nginx and PHP-FPM on port $PORT..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
