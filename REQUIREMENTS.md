# Clean2Go — Зависимости и требования

## Системные зависимости

| Пакет | Версия | Назначение | Команда установки |
|-------|--------|------------|-------------------|
| PHP | ≥ 8.2 | Runtime | `apt install php8.2` |
| MySQL / MariaDB | ≥ 8.0 | База данных | `apt install mysql-server` |
| Redis | ≥ 6.0 | Очереди (Horizon), кэш | `apt install redis-server redis-tools` |
| Node.js | ≥ 18 | Vite (фронтенд-сборка) | `apt install nodejs npm` |
| Composer | ≥ 2.0 | PHP-зависимости | [getcomposer.org](https://getcomposer.org) |
| Supervisor | любая | Фоновые процессы Horizon (production) | `apt install supervisor` |

### PHP-расширения

```
php-mbstring  php-xml  php-curl  php-zip  php-gd
php-mysql     php-redis  php-intl  php-bcmath
```

---

## PHP-пакеты (composer require)

Добавленные в процессе адаптации под российский рынок (сверх исходного Fixit):

| Пакет | Версия | Sprint | Назначение |
|-------|--------|--------|------------|
| `barryvdh/laravel-dompdf` | `^3.1` | Sprint 2 | Рендеринг договоров в PDF (Blade → HTML → PDF) |
| `laravel/horizon` | `^5.47` | Sprint 3 | UI + мониторинг очередей Redis; очередь `passport` для верификации |

> **DaData** используется через `Illuminate\Support\Facades\Http` напрямую (без SDK-пакета).  
> **nalog.ru** (проверка НПД) — то же самое, нативный HTTP-клиент Laravel.

### Установка после клонирования

```bash
mkdir -p ~/tmp
TMPDIR=~/tmp /usr/local/bin/composer install --ignore-platform-req=ext-grpc --ignore-platform-req=php
php artisan horizon:install   # публикует config/horizon.php и assets
```

> ⚠️ На PHP 8.5 нужен флаг `--ignore-platform-req=php` из-за `phpoffice/phpspreadsheet ^1.30` (ограничение `< 8.5`).

---

## Переменные окружения (.env)

Ключевые переменные, добавленные для российской адаптации:

### Платёжные шлюзы

```env
CLOUDPAYMENTS_PUBLIC_ID=pk_...
CLOUDPAYMENTS_API_SECRET=...
YOOKASSA_SHOP_ID=...
YOOKASSA_SECRET_KEY=...
```

### SMS-провайдеры

```env
SMSRU_API_KEY=...
SMSRU_SENDER=...        # опционально
SMSC_LOGIN=...
SMSC_PASSWORD=...
SMSC_SENDER=...         # опционально
```

### Очереди и Horizon

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
HORIZON_NAME=           # опционально, для multi-instance
HORIZON_DOMAIN=         # опционально
```

### Онбординг исполнителей

```env
ONBOARDING_MOCK_MODE=true   # false в production — включает реальные DaData + nalog.ru
```

> Ключи DaData, nalog.ru, Telegram-бот, паспортные провайдеры (Суфтех / Контур.Фокус)  
> хранятся **в базе данных** через таблицу `integration_settings`, не в `.env`.  
> Управляются через Admin → Настройки интеграций.

---

## Настройки в Admin → integration_settings

Ключи, которые нужно прописать через adminку после деплоя:

| Ключ | Пример | Назначение |
|------|--------|------------|
| `dadata_api_key` | `abc123` | DaData API-ключ (проверка ИНН/ЕГРЮЛ/ЕГРИП) |
| `dadata_secret_key` | `xyz` | DaData Secret-ключ |
| `passport_provider` | `manual` | Провайдер паспортной верификации: `manual` / `suftech` / `kontur` |
| `telegram_bot_token` | `123:ABC...` | Telegram-бот для уведомлений администратору |
| `telegram_admin_chat_id` | `-100123456` | Chat ID, куда слать уведомления |
| `contract_version_self_employed` | `1` | Версия договора самозанятого |
| `contract_version_ip` | `1` | Версия договора ИП |
| `contract_version_ooo` | `1` | Версия договора ООО |
| `contract_version_gph` | `1` | Версия договора ГПХ |

---

## Supervisor (production)

Конфиг создать в `/etc/supervisor/conf.d/horizon.conf`:

```ini
[program:horizon]
process_name=%(program_name)s
command=php /var/www/clean2go/fixit_laravel/artisan horizon
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/clean2go/fixit_laravel/storage/logs/horizon.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start horizon
```
