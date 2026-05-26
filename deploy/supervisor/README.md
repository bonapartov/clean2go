# Supervisor — деплой

## Установка

```bash
sudo apt install supervisor
sudo cp horizon.conf /etc/supervisor/conf.d/horizon.conf
```

Отредактируй пути если нужно (по умолчанию `/var/www/clean2go/fixit_laravel`):

```bash
sudo nano /etc/supervisor/conf.d/horizon.conf
```

## Запуск

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start horizon
sudo supervisorctl status
```

## После деплоя (обновление кода)

```bash
php artisan horizon:terminate   # Horizon перезапустится через Supervisor
```

## Логи

```bash
tail -f /var/www/clean2go/fixit_laravel/storage/logs/horizon.log
```
