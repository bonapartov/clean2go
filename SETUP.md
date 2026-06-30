# Развёртка Clean2Go на новой машине

## Требования

- PHP 8.2+
- MySQL 8.0+
- Node.js 18+
- Composer
- Git

## Шаги

### 1. Клонировать репозиторий

```bash
git clone https://github.com/bonapartov/clean2go.git
cd clean2go/fixit_laravel
git checkout claude/continue-russia-roadmap-Hl8t3
```

### 2. Установить зависимости

```bash
composer install
npm install
```

### 3. Создать базу данных MySQL

```sql
CREATE DATABASE fixit_data CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fixit'@'localhost' IDENTIFIED BY 'fixit123';
GRANT ALL PRIVILEGES ON fixit_data.* TO 'fixit'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Создать .env

```bash
cp .env.local .env
```

> Файл `.env.local` уже содержит все нужные настройки для локальной разработки.
> При необходимости отредактируй DB_HOST, DB_USERNAME, DB_PASSWORD.

### 5. Восстановить базу данных из дампа

```bash
gunzip -c database/dumps/local_dev_20260630.sql.gz | mysql -u fixit -pfixit123 fixit_data
```

> Если нужна свежая пустая БД вместо дампа:
> ```bash
> php artisan migrate --seed
> ```

### 6. Финальная настройка

```bash
php artisan key:generate        # только если .env не содержит APP_KEY
php artisan storage:link
npm run build
```

### 7. Запустить сервер

```bash
php artisan serve
```

Открыть: http://127.0.0.1:8000
Админка: http://127.0.0.1:8000/backend

---

## Текущая ветка разработки

`claude/continue-russia-roadmap-Hl8t3`

Последние изменения (Sprint 6):
- Карта зон на странице /backend/zone
- Дублирование зон
- Автозаполнение зон и комиссии при создании услуги
- Единое поле загрузки изображения для услуги
- Русские переводы DataTables
- Исправлен ERR_TOO_MANY_REDIRECTS
