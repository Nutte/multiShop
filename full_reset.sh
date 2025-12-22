#!/bin/bash
# FILE: full_reset.sh

echo "⚠️  WARNING: This will DESTROY all data in databases and storage!"
echo "⚠️  Make sure you have configured /etc/hosts as per MANUAL.md"
read -p "Press [Enter] to continue or Ctrl+C to abort..."

# 1. Остановка и очистка
echo "🛑 Stopping containers and removing volumes..."
docker compose down -v
# Удаляем файлы сессий и кэша, которые могли быть созданы с правами root
sudo rm -rf storage/framework/sessions/*
sudo rm -rf storage/framework/views/*
sudo rm -rf storage/framework/cache/*
sudo rm -rf storage/tenants
sudo rm -rf public/tenants

# 2. Пересборка и запуск
echo "🏗  Building and starting containers..."
# Проверка max_map_count для Elastic (частая ошибка)
if [ "$(sysctl -n vm.max_map_count)" -lt 262144 ]; then
    echo "❌ vm.max_map_count is too low for Elasticsearch."
    echo "👉 Run: sudo sysctl -w vm.max_map_count=262144"
    exit 1
fi

docker compose up -d --build

echo "⏳ Waiting for Database to initialize (15 seconds)..."
sleep 15

# 3. Установка зависимостей
echo "📦 Installing Composer dependencies..."
docker compose exec app composer install

# 4. Настройка приложения
echo "🔑 Generating Application Key..."
docker compose exec app php artisan key:generate

echo "🔗 Linking Storage..."
# Создаем стандартный линк public/storage
docker compose exec app php artisan storage:link
# Создаем линки тенантов public/tenants/{id}
docker compose exec app php artisan tenants:link

# 5. Очистка кэша (важно для конфигов доменов)
echo "🧹 Clearing caches..."
docker compose exec app php artisan optimize:clear

# 6. Миграция и Посев (САМОЕ ВАЖНОЕ)
echo "🌱 Migrating and Seeding (Public + Tenants)..."
# Внимание: здесь используется наша исправленная команда, которая
# сначала сеет Public (создает Админа), а потом магазины.
docker compose exec app php artisan tenants:migrate --fresh --seed

echo "✅ DONE! System is ready."
echo "👉 Admin Panel: http://admin.trishop.local"