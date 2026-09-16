#!/usr/bin/env sh
set -e

# Configure port for Nginx
export PORT=${PORT:-8080}
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

# Ensure storage directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# If APP_KEY is empty or default, generate one if needed
if [ -z "$APP_KEY" ]; then
    echo "Warning: APP_KEY is not set. Please set APP_KEY in your Railway environment variables."
fi

# Run caching and optimization if in production
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Run database migrations if database host is configured
if [ -n "$DB_HOST" ] || [ -n "$MYSQLHOST" ] || [ -n "$DATABASE_URL" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || echo "Migration failed or database not ready yet."
fi

echo "Starting Nginx and PHP-FPM on port $PORT..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
