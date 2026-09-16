#!/bin/sh
set -e

# Garantir diretórios e permissões do Laravel
mkdir -p /var/www/html/storage/framework/views /var/www/html/storage/framework/cache/data /var/www/html/storage/framework/sessions
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Executar migrações do banco e seed de usuários padrão
php artisan migrate --force || true
php artisan db:seed --force || true

exec "$@"
