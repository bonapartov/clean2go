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
- ~~Зачёркнуто~~ — убрано из плана

---

## Архитектурные решения (принятые)

| Вопрос | Решение |
|--------|---------|
| ИНН / ЕГРЮЛ / ЕГРИП | DaData — проверка и автодополнение |
| НПД (самозанятый) | nalog.ru напрямую (бесплатно). Если недоступен — пропустить, пометить `npd_status = pending`, перепроверить при следующем входе пользователя |
| Паспортная верификация | Суфтех **или** Контур.Фокус — переключатель в adminке (как с платёжными шлюзами). Начало Sprint 3 — ручная проверка, подключение провайдера в конце Sprint 3 |
| БИК банка | DaData |
| Адреса / зона работы | DaData (подсказки + автодополнение) |
| ФССП | ~~Убрано из плана~~ — нет юридического требования для данного типа бизнеса |
| ГПХ договор | Резервный вариант при потере НПД-статуса. `contract_type = 'gph'`. Платформа — налоговый агент: удерживает НДФЛ 13%, сверху платит страховые ~30% |
| Формат договоров | Blade-шаблоны + MPDF. Шаблоны в `resources/views/contracts/` (в git). Источники: `Modules/ProviderOnboarding/resources/contracts/source/*.docx`. Версия каждого типа в `integration_settings` (`contract_version_self_employed`, `_ip`, `_ooo`, `_gph`). Разработчик обновляет blade-файл и деплоит → admin инкрементирует версию в настройках → `payments_frozen = true` для затронутых пользователей → переподпись по OTP |
| PDF договоры | MPDF (`mpdf/mpdf`) |
| Очереди | Laravel Queue + Redis + Horizon |
| Push-уведомления Flutter | Существующий `Modules/Firebase/` |
| SMS | Существующий `Modules/Smsru/` |

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
| Миграция: удалить записи Plat.ru из integration_settings | ✅ | `Database/Migrations/2026_05_26_000001_*` |
| `EnsureOnboardingComplete` middleware | ✅ | `Http/Middleware/EnsureOnboardingComplete.php` |
| `IntegrationSetting` модель + `get()/set()` с шифрованием | ✅ | `Models/IntegrationSetting.php` |
| `ProviderVerification` модель | ✅ | `Models/ProviderVerification.php` |
| `OnboardingLog` модель + `record()` | ✅ | `Models/OnboardingLog.php` |
| `InnVerificationService` — mock-режим | ✅ | `Services/InnVerificationService.php` |
| API Шаг 1: `POST /api/onboarding/inn` | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| API Шаг 6: `POST /api/onboarding/specialization` | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| API Шаг 7: `GET /api/onboarding/complete` | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| API `GET /api/onboarding/status` (Flutter polling) | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| Admin: настройки интеграций | ✅ | `Http/Controllers/Backend/OnboardingSettingsController.php` |
| Admin: список верификаций с фильтрами | ✅ | `Http/Controllers/Backend/VerificationController.php` |
| Admin: карточка верификации | ✅ | `Http/Controllers/Backend/VerificationController.php` |
| Admin: approve / reject / request_docs | ✅ | `Http/Controllers/Backend/VerificationController.php` |
| Backend view: настройки | ✅ | `resources/views/backend/onboarding/settings/index.blade.php` |
| Backend view: список верификаций | ✅ | `resources/views/backend/onboarding/verifications/index.blade.php` |
| Backend view: карточка верификации | ✅ | `resources/views/backend/onboarding/verifications/show.blade.php` |
| Routes: `api.php` + `backend.php` | ✅ | `Routes/` |
| `ProviderOnboardingServiceProvider` | ✅ | `Providers/ProviderOnboardingServiceProvider.php` |

---

## Sprint 2 — Реальные API + договор

**Статус: ЗАВЕРШЁН ✅**

> ⚠️ **Единственный незакрытый пункт:** текст юридических разделов из `.docx` ещё не перенесён в blade-шаблоны.
> Вставить вручную — открыть каждый `.docx` из `doc/` и скопировать разделы (HTML `<p>`) на место `{{-- TODO --}}` в `resources/views/contracts/*.blade.php`.
> Все четыре файла: `self_employed`, `ip`, `ooo`, `gph`. Код и переменные уже готовы — нужен только юридический текст.

### Внешние блокеры (решить до кодинга)

| Блокер | Статус | Примечание |
|--------|--------|------------|
| Зарегистрироваться на DaData.ru, получить API-ключ и Secret-ключ | ✅ | Ключи внесены в adminку через `integration_settings` |
| Юридически проверенные шаблоны договоров (самозанятый / ИП / ООО / ГПХ) | ✅ | Все четыре шаблона готовы — `source/*.docx` в репозитории |

### Задачи Sprint 2

| Задача | Статус | Файл |
|--------|--------|------|
| DaData: используется `Http::post()` напрямую, без SDK-пакета | ✅ | `Services/InnVerificationService.php` |
| `InnVerificationService` — реальный DaData (ЕГРЮЛ/ЕГРИП/ИНН физлица) | ✅ | `Services/InnVerificationService.php` |
| `NpdVerificationService` — nalog.ru напрямую + fallback при недоступности | ✅ | `Services/NpdVerificationService.php` |
| `CheckPendingNpdOnLogin` listener — retry НПД при входе если `npd_status = pending` | ✅ | `Listeners/CheckPendingNpdOnLogin.php` |
| PDF-рендеринг: используется существующий `barryvdh/laravel-dompdf` вместо mpdf | ✅ | `composer.json` |
| Blade-шаблоны: HTML-структура + переменные готовы, **текст из `.docx` вставить вручную** | ⚠️ | `resources/views/contracts/` |
| `ContractService` — рендерит Blade → HTML → DomPDF, подставляет переменные провайдера | ✅ | `Services/ContractService.php` |
| Миграция: `contract_version`, `payments_frozen`, `npd_status=pending`, `contract_type=gph` | ✅ | `Database/Migrations/2026_05_26_000002_*` |
| `IntegrationSettingsSeeder` — версии шаблонов: `contract_version_self_employed`, `_ip`, `_ooo`, `_gph` | ✅ | `Database/Seeders/IntegrationSettingsSeeder.php` |
| API Шаг 5a: `POST /api/onboarding/contract/generate` | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| API Шаг 5b: `POST /api/onboarding/contract/send-sms` | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| API Шаг 5c: `POST /api/onboarding/contract/sign` (SMS OTP) | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| Routes Sprint 2 раскомментированы + ГПХ-маршруты добавлены | ✅ | `Routes/api.php` |
| Флоу ИП на НПД (`ip_on_npd`): ОКВЭД-предупреждение — `okved_warning` в ответе шага 1 | ✅ | `Services/InnVerificationService.php` |
| Сохранение специализации в реальные категории (Шаг 6) | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| `ContractService` — шаблон ГПХ с физлицом (4-й тип договора) | ✅ | `Services/ContractService.php` |
| API `POST /api/onboarding/npd-lost` — фиксировать потерю НПД, выставить `payments_frozen = true` | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| API `POST /api/onboarding/gph-contract/generate` — генерация ГПХ PDF при потере НПД | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| API `POST /api/onboarding/gph-contract/sign` — подписание ГПХ по SMS OTP, снять `payments_frozen` | ✅ | `Http/Controllers/Api/OnboardingController.php` |

---

## Sprint 3 — Паспортная верификация + очереди

**Статус: ЗАВЕРШЁН ✅**

### Внешние блокеры

| Блокер | Статус | Примечание |
|--------|--------|------------|
| Redis в production (`redis-cli ping`) | ✅ | Установлен, `QUEUE_CONNECTION=redis` |
| Supervisor конфиг для Laravel Horizon | ✅ | `deploy/supervisor/horizon.conf` готов |
| Получить доступ к Суфтех **или** Контур.Фокус (или обоим) | ❌ | Начинаем с ручной проверки, подключаем провайдера в конце Sprint 3 |

### Задачи Sprint 3

| Задача | Статус | Файл |
|--------|--------|------|
| `QUEUE_CONNECTION=redis` в `.env` | ✅ | `.env` |
| `composer require laravel/horizon` + `php artisan horizon:install` | ✅ | `composer.json` |
| Supervisor конфиг для Horizon в production | ✅ | `deploy/supervisor/horizon.conf` |
| `VerifyPassportJob` (очередь `passport`, 3 попытки, timeout 120с) | ✅ | `Jobs/VerifyPassportJob.php` |
| `PassportVerificationService` — интерфейс + фабрика провайдеров | ✅ | `Services/PassportVerificationService.php` |
| `PassportProviderInterface` | ✅ | `Services/Passport/PassportProviderInterface.php` |
| `PassportVerificationService` — реализация: ручная проверка (`manual_review`) | ✅ | `Services/Passport/ManualPassportProvider.php` |
| `PassportVerificationService` — реализация: Суфтех | ❌ | `Services/Passport/SuftechPassportProvider.php` |
| `PassportVerificationService` — реализация: Контур.Фокус | ❌ | `Services/Passport/KonturPassportProvider.php` |
| Переключатель активного паспортного провайдера в adminке | ✅ | `integration_settings`: `passport_provider = manual\|suftech\|kontur` |
| API Шаг 3: `POST /api/onboarding/passport` (загрузка файлов + dispatch Job) | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| API `GET /api/onboarding/passport/status` (Flutter polling каждые 30с) | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| `OnboardingStepCompleted` Event | ✅ | `Events/OnboardingStepCompleted.php` |
| `OnboardingCompleted` Event | ✅ | `Events/OnboardingCompleted.php` |
| `NotifyAdminOnManualReview` Listener | ✅ | `Listeners/NotifyAdminOnManualReview.php` |
| Email уведомления администратору (manual_review) | ✅ | `Listeners/NotifyAdminOnManualReview.php` |
| Telegram уведомления администратору | ✅ | `Listeners/NotifyAdminOnManualReview.php` (ключи через `integration_settings`) |
| Push-уведомление Flutter через `Modules/Firebase/` при завершении Job | ✅ | `Jobs/VerifyPassportJob.php` |
| Раскомментировать routes Sprint 3 в `api.php` | ✅ | `Routes/api.php` |
| Безопасный доступ к файлам паспортов: Laravel Signed Routes (TTL 15 мин.) | ✅ | `Routes/api.php` + `servePassportFile()` |

---

## Sprint 4 — ООО + полнота

**Статус: ЧАСТИЧНО ЗАВЕРШЁН 🔄** (бэкенд готов, ждут внешние зависимости)

### Внешние блокеры

| Блокер | Статус | Примечание |
|--------|--------|------------|
| Модуль выплат: публичный хук/событие для проверки НПД перед выплатой | ❌ | Разработчик модуля выплат должен предоставить точку интеграции (Event или Service call) до начала задачи «Повторная проверка НПД перед выплатой» |
| Модуль выплат: поддержка удержания НДФЛ для `contract_type = gph` | ❌ | Логика `amount × 0.87` реализуется на стороне модуля выплат; ProviderOnboarding только передаёт `contract_type` |

### Задачи Sprint 4

| Задача | Статус | Файл |
|--------|--------|------|
| API Шаг 2В: `POST /api/onboarding/ooo-documents` (директор + доверенность + БИК) | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| `BikLookupService` (DaData: БИК → банк / коррсчёт) | ✅ | `Services/BikLookupService.php` |
| Полный флоу ООО (Шаг 2В → pending_manual → менеджер → Шаг 5 → ...) | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| Rate limiting: `/api/onboarding/inn` — 5 req/час/IP | ✅ | `app/Providers/AppServiceProvider.php` |
| Rate limiting: SMS — 3/час/номер, sign — 3 попытки + 15 мин. блок | ✅ | `app/Providers/AppServiceProvider.php` |
| Scheduler: автоудаление файлов по ФЗ-152 | ✅ | `Console/Commands/PurgePassportFiles.php` + `Kernel.php` |
| Повторная проверка НПД перед каждой выплатой исполнителю | ❌ | `Listeners/` + модуль выплат |
| Модуль выплат: удержание НДФЛ для ГПХ — `amount × 0.87`, платёж исполнителю нетто | ❌ | модуль выплат |
| Flutter: экран заморозки выплат — 3 варианта (восстановить НПД / стать ИП / подписать ГПХ) | ❌ | Flutter (описание логики в ROADMAP.md) |
| `onboarding_logs`: запись при каждой смене `contract_type` с причиной | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| Итоговые тесты: обратная совместимость, все типы налогоплательщиков | ✅ | `tests/Feature/OnboardingFlowTest.php` (14 тестов, все зелёные) |

---

## Архитектура (справочник)

### Типы налогоплательщиков и их путь

| Тип | ИНН | Шаги |
|-----|-----|------|
| `self_employed` | 12 цифр, НПД активен | 1 → 3 → 5 → 6 → 7 |
| `individual_entrepreneur` | 12 цифр, ЕГРИП | 1 → 2Б → 3 → 5 → 6 → 7 |
| `ip_on_npd` | 12 цифр, ЕГРИП + НПД | 1 → 2Б → 3 → 5 → 6 → 7 |
| `legal_entity` | 10 цифр, ЕГРЮЛ | 1 → 2В → pending_manual → 3 → 5 → 6 → 7 |

> ⚠️ **Шаг 2 для `self_employed` пропускается** — самозанятому не нужно ОКВЭД-предупреждение.  
> `InnVerificationService` уже возвращает `next_step: 3` для самозанятых.  
> Flutter управляет переходом. Бэкенд не блокирует переход к шагу 3 без шага 2 — это ожидаемое поведение, не баг.  
> Проверить при интеграционном тесте Sprint 2: самозанятый после шага 1 не должен видеть экран шага 2.

### НПД — логика проверки

- Проверяется через nalog.ru напрямую (бесплатно)
- Если nalog.ru недоступен → `npd_status = pending`, онбординг не блокируется
- При следующем входе пользователя → автоматическая повторная проверка
- При успехе → `npd_status = active`, пометка «подтверждён»

### ГПХ — логика при потере НПД

Triggered: `CheckPendingNpdOnLogin` или ручной вызов `POST /api/onboarding/npd-lost` возвращает `npd_status = lost`.

1. Выплаты исполнителю **замораживаются** (`payments_frozen = true`)
2. Flutter показывает экран с 3 вариантами:
   - **Восстановить НПД** — самостоятельно через nalog.ru, затем повторная проверка
   - **Стать ИП** — переоформить анкету, пройти шаг 2Б заново
   - **Подписать ГПХ** — продолжить как физлицо, платформа становится налоговым агентом
3. При выборе ГПХ:
   - `contract_type` меняется с `self_employed` на `'gph'`
   - Генерируется новый PDF (шаблон ГПХ с физлицом)
   - Подписание по SMS OTP (тот же механизм, что и Sprint 2)
   - Запись в `onboarding_logs` с причиной и предыдущим `contract_type`
4. Расчёт выплат по ГПХ:
   - `net_amount = gross_amount × 0.87` (удержание НДФЛ 13%)
   - Платформа дополнительно платит страховые взносы ~30% сверху

> ✅ Шаблон ГПХ получен (`contract_gph.docx`), источник в `source/`.

### Паспорт — логика провайдеров

Единый интерфейс `PassportProviderInterface`, три реализации:
- `manual` — admin проверяет вручную (default для старта)
- `suftech` — автоматически через Суфтех API
- `kontur` — автоматически через Контур.Фокус API

Активный провайдер выбирается в adminке через `integration_settings.passport_provider`.

### API эндпоинты

| Метод | URL | Sprint | Статус |
|-------|-----|--------|--------|
| GET | `/api/onboarding/status` | 1 | ✅ |
| POST | `/api/onboarding/inn` | 1 | ✅ |
| POST | `/api/onboarding/specialization` | 1 | ✅ |
| GET | `/api/onboarding/complete` | 1 | ✅ |
| POST | `/api/onboarding/contract/generate` | 2 | ✅ |
| POST | `/api/onboarding/contract/send-sms` | 2 | ✅ |
| POST | `/api/onboarding/contract/sign` | 2 | ✅ |
| POST | `/api/onboarding/passport` | 3 | ✅ |
| GET | `/api/onboarding/passport/status` | 3 | ✅ |
| GET | `/api/onboarding/passport/file/{userId}/{type}` | 3 | ✅ |
| POST | `/api/onboarding/ooo-documents` | 4 | ✅ |
| POST | `/api/onboarding/npd-lost` | 2 | ✅ |
| POST | `/api/onboarding/gph-contract/generate` | 2 | ✅ |
| POST | `/api/onboarding/gph-contract/sign` | 2 | ✅ |

### Mock-режим (Sprint 1)

`InnVerificationService` работает в mock-режиме пока `ONBOARDING_MOCK_MODE=true` в `.env`.

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
| nalog.ru недоступен при онбординге | НПД не проверяется сразу | Fallback: `npd_status = pending`, перепроверка при входе |
| DaData API-ключ не получен до Sprint 2 | `InnVerificationService` остаётся на mock | Зарегистрироваться заранее на dadata.ru |
| Версия договора изменилась, пользователь не переподписал | Работа без актуального договора | Admin инкрементирует `contract_version_*` в настройках после деплоя — это автоматически выставляет `payments_frozen = true` затронутым пользователям |
| ~~Шаблон ГПХ не получен от юриста до Sprint 4~~ | ~~Sprint 4 не может начаться~~ | ✅ `contract_gph.docx` получен, лежит в `source/` |
| Horizon без Supervisor в production | Jobs останавливаются после перезапуска сервера | Добавить Supervisor конфиг до Sprint 3 |
| Суфтех и Контур.Фокус — получение доступа занимает время | Sprint 3 начнётся с ручной проверкой | Подавать заявки заранее, параллельно с Sprint 2 |
| ~~Шаблон ГПХ без юридической проверки~~ | ~~Договор юридически недействителен~~ | ✅ Шаблон получен |
| НДФЛ-агент по ГПХ — дополнительная отчётность | 6-НДФЛ, реестры выплат физлицам в ФНС | Уточнить у бухгалтера объём отчётности до реализации |
