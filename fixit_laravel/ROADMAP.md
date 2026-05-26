# Fixit Russia — Дорожная карта разработки

**Проект:** Онбординг исполнителей (Provider & Serviceman Onboarding Module)  
**Модуль:** `Modules/ProviderOnboarding/`  
**Последнее обновление:** 2026-05-26

---

## Легенда

- ✅ Сделано
- 🔄 В процессе
- ❌ Не начато
- ⚠️ Заблокировано (есть внешняя зависимость)

---

## Sprint 1 — Инфраструктура + самозанятый (mock)

**Статус: ЗАВЕРШЁН ✅**  
**Коммит:** `dfb2013` feat: Sprint 1 — ProviderOnboarding module infrastructure (2026-05-25)

| Задача | Статус | Файл |
|--------|--------|------|
| Структура модуля `Modules/ProviderOnboarding/` | ✅ | `Modules/ProviderOnboarding/` |
| Миграция: `alter_users_add_onboarding` | ✅ | `Database/Migrations/2026_05_25_000001_*` |
| Миграция: `create_integration_settings_table` | ✅ | `Database/Migrations/2026_05_25_000002_*` |
| Миграция: `create_provider_verifications_table` | ✅ | `Database/Migrations/2026_05_25_000003_*` |
| Миграция: `create_onboarding_logs_table` | ✅ | `Database/Migrations/2026_05_25_000004_*` |
| `EnsureOnboardingComplete` middleware | ✅ | `Http/Middleware/EnsureOnboardingComplete.php` |
| `IntegrationSetting` модель + `get()/set()` с шифрованием | ✅ | `Models/IntegrationSetting.php` |
| `ProviderVerification` модель | ✅ | `Models/ProviderVerification.php` |
| `OnboardingLog` модель + `record()` | ✅ | `Models/OnboardingLog.php` |
| `InnVerificationService` — mock-режим | ✅ | `Services/InnVerificationService.php` |
| API Шаг 1: `POST /api/onboarding/inn` | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| API Шаг 6: `POST /api/onboarding/specialization` | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| API Шаг 7: `GET /api/onboarding/complete` | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| API `GET /api/onboarding/status` (Flutter polling) | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| Admin: настройки интеграций (6 групп) | ✅ | `Http/Controllers/Backend/OnboardingSettingsController.php` |
| Admin: список верификаций с фильтрами | ✅ | `Http/Controllers/Backend/VerificationController.php` |
| Admin: карточка верификации | ✅ | `Http/Controllers/Backend/VerificationController.php` |
| Admin: approve / reject / request_docs | ✅ | `Http/Controllers/Backend/VerificationController.php` |
| Backend view: настройки | ✅ | `resources/views/backend/onboarding/settings/index.blade.php` |
| Backend view: список верификаций | ✅ | `resources/views/backend/onboarding/verifications/index.blade.php` |
| Backend view: карточка верификации | ✅ | `resources/views/backend/onboarding/verifications/show.blade.php` |
| Routes: `api.php` + `backend.php` | ✅ | `Routes/` |
| `ProviderOnboardingServiceProvider` | ✅ | `Providers/ProviderOnboardingServiceProvider.php` |

### Проверено на Sprint 1

- [x] `redis-cli ping` на сервере — **нужно проверить перед Sprint 3**
- [x] `nwidart/laravel-modules` установлен

---

## Sprint 2 — Реальные API + договор

**Статус: НЕ НАЧАТ ❌**

### Внешние блокеры (решить до кодинга)

| Блокер | Статус | Примечание |
|--------|--------|------------|
| Зарегистрироваться на DaData.ru и получить API-ключ | ❌ | Без этого `InnVerificationService` остаётся на mock |
| Юридически проверенные PDF-шаблоны договоров (самозанятый / ИП / ООО) | ❌ | Без этого `ContractService` не может быть завершён |

### Задачи Sprint 2

| Задача | Статус | Файл |
|--------|--------|------|
| DaData PHP SDK: `composer require dadata/dadata-php` | ❌ | `composer.json` |
| `InnVerificationService` — реальный DaData (ЕГРЮЛ/ЕГРИП/ИНН физлица) | ❌ | `Services/InnVerificationService.php` |
| `NpdVerificationService` — Plat.ru | ❌ | `Services/NpdVerificationService.php` |
| MPDF: `composer require mpdf/mpdf` | ❌ | `composer.json` |
| `ContractService` — генерация PDF по шаблону | ❌ | `Services/ContractService.php` |
| API Шаг 5a: `POST /api/onboarding/contract/generate` | ❌ | `Http/Controllers/Api/OnboardingController.php` |
| API Шаг 5b: `POST /api/onboarding/contract/send-sms` | ❌ | `Http/Controllers/Api/OnboardingController.php` |
| API Шаг 5c: `POST /api/onboarding/contract/sign` (SMS OTP) | ❌ | `Http/Controllers/Api/OnboardingController.php` |
| Раскомментировать routes Sprint 2 в `api.php` | ❌ | `Routes/api.php` |
| Флоу ИП на НПД (`ip_on_npd`): ОКВЭД-предупреждение (Шаг 2Б) | ❌ | `Http/Controllers/Api/OnboardingController.php` |
| Сохранение специализации в реальные категории (Шаг 6, Sprint 1 пишет в лог) | ❌ | `Http/Controllers/Api/OnboardingController.php` |

---

## Sprint 3 — Верификация документов

**Статус: НЕ НАЧАТ ❌**

### Внешние блокеры

| Блокер | Статус | Примечание |
|--------|--------|------------|
| Redis в production (`redis-cli ping`) | ❌ | Если нет → `apt install redis-server` |
| Supervisor конфиг для Laravel Horizon | ❌ | Нужен до запуска в production |
| Выбор паспортного провайдера (Суфтех / Контур.Фокус) и получение доступа | ❌ | Начинаем с `passport_provider = 'Ручная'`, интеграция в конце Sprint 3 |
| ФССП API: уточнить доступ (fssp.gov.ru требует аккредитацию) | ❌ | Рассмотреть агрегаторы: Контур, SmartDeal |

### Задачи Sprint 3

| Задача | Статус | Файл |
|--------|--------|------|
| `QUEUE_CONNECTION=redis` в `.env` | ❌ | `.env` |
| `composer require laravel/horizon` + `php artisan horizon:install` | ❌ | `composer.json` |
| Supervisor конфиг для Horizon в production | ❌ | `deploy/supervisor/` |
| `VerifyPassportJob` (очередь `passport`, 3 попытки, timeout 120с) | ❌ | `Jobs/VerifyPassportJob.php` |
| `PassportVerificationService` — ручная проверка (manual_review) | ❌ | `Services/PassportVerificationService.php` |
| `PassportVerificationService` — интеграция Суфтех/Контур.Фокус | ❌ | `Services/PassportVerificationService.php` |
| `CheckFsspJob` (очередь `fssp`) | ❌ | `Jobs/CheckFsspJob.php` |
| `FsspVerificationService` | ❌ | `Services/FsspVerificationService.php` |
| API Шаг 3: `POST /api/onboarding/passport` (загрузка файлов + dispatch Job) | ❌ | `Http/Controllers/Api/OnboardingController.php` |
| API `GET /api/onboarding/passport/status` (Flutter polling каждые 30с) | ❌ | `Http/Controllers/Api/OnboardingController.php` |
| `OnboardingStepCompleted` Event | ❌ | `Events/OnboardingStepCompleted.php` |
| `OnboardingCompleted` Event | ❌ | `Events/OnboardingCompleted.php` |
| `NotifyAdminOnManualReview` Listener | ❌ | `Listeners/NotifyAdminOnManualReview.php` |
| Email уведомления администратору (manual_review, has_debts) | ❌ | `Listeners/NotifyAdminOnManualReview.php` |
| Telegram уведомления администратору | ❌ | `Listeners/NotifyAdminOnManualReview.php` |
| Push-уведомление Flutter через `Modules/Firebase/` при завершении Job | ❌ | `Jobs/VerifyPassportJob.php` |
| Повторная проверка НПД перед выплатами (через Events/Listeners) | ❌ | `Events/`, `Listeners/` |
| Раскомментировать routes Sprint 3 в `api.php` | ❌ | `Routes/api.php` |
| Безопасный доступ к файлам паспортов: Laravel Signed Routes (TTL 15 мин.) | ❌ | `Routes/api.php` |

---

## Sprint 4 — ООО + полнота

**Статус: НЕ НАЧАТ ❌**

| Задача | Статус | Файл |
|--------|--------|------|
| API Шаг 2В: `POST /api/onboarding/ooo-documents` (директор + доверенность + БИК) | ❌ | `Http/Controllers/Api/OnboardingController.php` |
| `BikLookupService` (DaData: БИК → банк / коррсчёт) | ❌ | `Services/BikLookupService.php` |
| Полный флоу ООО (Шаг 2В → pending_manual → менеджер → Шаг 5 → ...) | ❌ | `Http/Controllers/Api/OnboardingController.php` |
| Rate limiting: `/api/onboarding/inn` — 5 req/час/IP | ❌ | `Routes/api.php` |
| Rate limiting: SMS — 3/час/номер, sign — 3 попытки + 15 мин. блок | ❌ | `Routes/api.php` |
| Scheduler: автоудаление файлов по ФЗ-152 (`php artisan gdpr:purge`) | ❌ | `app/Console/Kernel.php` |
| Итоговые тесты: обратная совместимость, все типы налогоплательщиков | ❌ | `tests/` |

---

## Архитектура (справочник)

### Типы налогоплательщиков и их путь

| Тип | ИНН | Шаги |
|-----|-----|------|
| `self_employed` | 12 цифр, НПД активен | 1 → 3 → 4(авто) → 5 → 6 → 7 |
| `individual_entrepreneur` | 12 цифр, ЕГРИП | 1 → 2Б → 3 → 4(авто) → 5 → 6 → 7 |
| `ip_on_npd` | 12 цифр, ЕГРИП + НПД | 1 → 2Б → 3 → 4(авто) → 5 → 6 → 7 |
| `legal_entity` | 10 цифр, ЕГРЮЛ | 1 → 2В → pending_manual → 3 → 4(авто) → 5 → 6 → 7 |

### API эндпоинты

| Метод | URL | Sprint | Статус |
|-------|-----|--------|--------|
| GET | `/api/onboarding/status` | 1 | ✅ |
| POST | `/api/onboarding/inn` | 1 | ✅ |
| POST | `/api/onboarding/specialization` | 1 | ✅ |
| GET | `/api/onboarding/complete` | 1 | ✅ |
| POST | `/api/onboarding/ooo-documents` | 4 | ❌ |
| POST | `/api/onboarding/contract/generate` | 2 | ❌ |
| POST | `/api/onboarding/contract/send-sms` | 2 | ❌ |
| POST | `/api/onboarding/contract/sign` | 2 | ❌ |
| POST | `/api/onboarding/passport` | 3 | ❌ |
| GET | `/api/onboarding/passport/status` | 3 | ❌ |

### Mock-режим (Sprint 1)

`InnVerificationService` работает в mock-режиме пока `ONBOARDING_MOCK_MODE=true` в `.env` (или `config/provider-onboarding.php`).

Эмуляция по последней цифре 12-значного ИНН:
- `*0` → ИНН не найден (ошибка)
- `*1`, `*2`, `*3` → ИП
- `*4` → ИП на НПД
- `*5`–`*9` → Самозанятый

10-значный ИНН → ООО.

---

## Риски

| Риск | Влияние | Митигация |
|------|---------|-----------|
| ФССП API требует аккредитацию с 2024 | Sprint 3 заблокирован | Рассмотреть Контур/SmartDeal как агрегатор |
| DaData API-ключ не получен | Sprint 2 остаётся на mock | Зарегистрироваться заранее |
| PDF-шаблоны без юр. проверки | Договора недействительны | Получить шаблоны до начала Sprint 2 |
| Horizon без Supervisor в production | Jobs не обрабатываются после перезапуска | Добавить конфиг до Sprint 3 |
| Повторная НПД-проверка перед выплатами | Затрагивает модуль выплат вне ProviderOnboarding | Только через Events/Listeners (Premise 1) |
