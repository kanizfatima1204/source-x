#!/usr/bin/env sh
set -e

# Configure port for Nginx
export PORT=${PORT:-8080}
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/conf.d/default.conf

# Ensure storage and database directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/database
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Fallback environment variables if not supplied
export APP_KEY="${APP_KEY:-base64:l7E+6brnMSvP9dnx6QOumHLSLB1auRcsSpKff3HGtLo=}"
export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"

# Configure database: Use MySQL if environment variables exist, otherwise SQLite
if [ -n "$MYSQLHOST" ] || [ -n "$DB_HOST" ] || [ -n "$DATABASE_URL" ] || [ -n "$MYSQL_URL" ]; then
    echo "Connecting to configured MySQL database..."
    export DB_CONNECTION=mysql
else
    echo "No MySQL variables detected. Preparing SQLite database..."
    export DB_CONNECTION=sqlite
    export DB_DATABASE="/var/www/html/database/database.sqlite"
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
    chmod 664 /var/www/html/database/database.sqlite
fi

# Clear any stale cached configuration
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Always run database migrations and seeds
echo "Running database migrations..."
php artisan migrate --force

echo "Seeding database with default accounts (admin@source-x.test)..."
php artisan db:seed --force || echo "Seeder finished or already seeded."

# Cache routes and views in production
if [ "$APP_ENV" = "production" ]; then
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "Starting Nginx and PHP-FPM on port $PORT..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
