#!/bin/bash
set -e

# Instala vendor se não existir (bind mount sobrescreve o do build)
if [ ! -f vendor/autoload.php ]; then
    echo "Installing dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Gera APP_KEY se estiver vazia
if [ -f .env ] && grep -q '^APP_KEY=$' .env; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Copia .env se não existir
if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
    php artisan key:generate --force
fi

# Aguarda o banco estar pronto e roda migrations
echo "Running migrations..."
php artisan migrate --force 2>/dev/null || true

exec "$@"
