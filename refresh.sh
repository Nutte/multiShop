#!/bin/bash
# FILE: refresh.sh

# Останавливаем выполнение при любой ошибке
set -e

echo "🔄 Starting heavy refresh..."

# 1. Сброс кэшей Laravel
echo "🧹 Clearing caches..."
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan config:clear

# 2. Пересоздание БД и Сидинг
echo "🌱 Migrating and Seeding (Public + Tenants)..."
# ВАЖНО: --fresh сносит все таблицы, чтобы избежать конфликтов миграций
docker compose exec app php artisan tenants:migrate --fresh --seed

# 3. Обновление симлинков
echo "🔗 Linking storage..."
docker compose exec app php artisan tenants:link --force

echo "✅ Refresh complete! Admin: http://admin.trishop.local"