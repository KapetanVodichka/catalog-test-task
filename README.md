# Catalog Test Task

Тестовое приложение: каталог товаров с админ-панелью.
Все обязательные и бонусные требования выполнены

## Стек

- PHP 8.4
- Laravel 13
- Sanctum
- PostgreSQL 17
- Vue 3 + Inertia.js
- Tailwind CSS 4
- Docker

## Запуск проекта

Первый раз поднять контейнеры:

```bash
docker compose up -d --build
```

Последующие разы для запуска контейнеров:
```bash
docker compose up -d
```

При старте докера автоматически выполняются:

- `composer install` (если `vendor` отсутствует)
- `npm ci` (если `node_modules` отсутствует)
- `npm run build`
- `php artisan migrate --force --seed`

Открыть приложение:

- `http://localhost:8080`

## Доступ в админку

- Email: `admin@example.com`
- Password: `password`

## Что создается автоматически сидерами

- 1 пользователь (`admin@example.com`)
- 6 категорий
- 60 товаров

## Полезные команды

Тесты:

```bash
docker compose exec -T app php artisan test
```
