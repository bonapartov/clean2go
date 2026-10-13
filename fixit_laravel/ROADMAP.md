# Fixit Russia — Дорожная карта разработки

**Проект:** Онбординг исполнителей (Provider & Serviceman Onboarding Module)  
**Модуль:** `Modules/ProviderOnboarding/`  
**Последнее обновление:** 2026-10-13

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

> ✅ Blade-шаблоны договоров содержат полный юридический текст из `.docx` с Blade-переменными. Версия от 2026-06-14.

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
| Blade-шаблоны: HTML-структура + переменные + текст из `.docx` | ✅ | `resources/views/contracts/` |
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

**Статус: ЗАВЕРШЁН ✅** (Flutter-экраны — в Sprint 5)

### Задачи Sprint 4

| Задача | Статус | Файл |
|--------|--------|------|
| API Шаг 2В: `POST /api/onboarding/ooo-documents` (директор + доверенность + БИК) | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| `BikLookupService` (DaData: БИК → банк / коррсчёт) | ✅ | `Services/BikLookupService.php` |
| Полный флоу ООО (Шаг 2В → pending_manual → менеджер → Шаг 5 → ...) | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| Rate limiting: `/api/onboarding/inn` — 5 req/час/IP | ✅ | `app/Providers/AppServiceProvider.php` |
| Rate limiting: SMS — 3/час/номер, sign — 3 попытки + 15 мин. блок | ✅ | `app/Providers/AppServiceProvider.php` |
| Scheduler: автоудаление файлов по ФЗ-152 | ✅ | `Console/Commands/PurgePassportFiles.php` + `Kernel.php` |
| Блокировка начисления комиссии при `payments_frozen = true` | ✅ | `app/Http/Traits/CommissionTrait.php` |
| Удержание НДФЛ 13% для `contract_type = gph` при начислении комиссии | ✅ | `app/Http/Traits/CommissionTrait.php` |
| Блокировка вывода средств при `payments_frozen = true` | ✅ | `app/Repositories/Backend/WithdrawRequestRepository.php` |
| `onboarding_logs`: запись при каждой смене `contract_type` с причиной | ✅ | `Http/Controllers/Api/OnboardingController.php` |
| Итоговые тесты: обратная совместимость, все типы налогоплательщиков | ✅ | `tests/Feature/OnboardingFlowTest.php` (16 тестов, все зелёные) |

---

## Sprint 5 — Flutter-онбординг (fixit_provider)

**Статус: ЗАВЕРШЁН ✅**  
**Коммиты:** `c76371c` feat: Sprint 5 — Flutter-экраны онбординга исполнителей (2026-05-27), `8de628c` fix: 94 ошибки undefined_named_parameter, `cf4d377` fix: восстановлены экраны и конфликты стабов (2026-05-28)

### Задачи Sprint 5

| Задача | Статус | Файл |
|--------|--------|------|
| API methods для `/api/onboarding/*` | ✅ | `lib/services/api_methods.dart` |
| `OnboardingProvider` — state management | ✅ | `lib/providers/app_pages_provider/onboarding_provider.dart` |
| Экран Шаг 1: ввод и проверка ИНН | ✅ | `lib/screens/.../onboarding_inn_screen.dart` |
| Экран Шаг 3: загрузка паспорта | ✅ | `lib/screens/.../onboarding_passport_screen.dart` |
| Экран ожидания проверки паспорта | ✅ | `lib/screens/.../onboarding_pending_screen.dart` |
| Экран Шаг 5: подписание договора (SMS OTP) | ✅ | `lib/screens/.../onboarding_contract_screen.dart` |
| Экран заморозки выплат (3 варианта) | ✅ | `lib/screens/.../payments_frozen_screen.dart` |
| Хук после логина — редирект на онбординг | ✅ | `lib/providers/auth_provider/login_as_provider_provider.dart` |
| Регистрация маршрутов и провайдера | ✅ | `route_name.dart`, `route_method.dart`, `main.dart` |
| Фикс DaData: Bearer → Token | ✅ | `InnVerificationService.php` |
| Исправлены 94 ошибки компиляции (layout-стабы) | ✅ | `_missing_stubs.dart` + 33 layout-файла |
| UI-тестирование на реальном устройстве | ❌ | — |

---

## План от 13.10.2026 — агентская модель B2C

**Статус: решения приняты, реализация не начата.** Платформа становится информационным посредником с комиссией: договор на услугу клиент заключает с исполнителем, чеки и акты выдаёт исполнитель, деньги клиента защищены номинальным счётом. Сверяться с этим разделом перед планированием каждого спринта. Обоснования, юрлицо, налоги и ОКВЭД — в документе [«Clean2Go — решения по юрлицу, налогам и модели B2C»](https://claude.ai/artifact/7eU3pKWZ4kwGoCUDa9bzKt). Весь код правим в этом репозитории.

### Порядок

1. **Sprint 6** — то, что не ждёт никого снаружи. Начинать сейчас.
2. **Sprint 7** — договор и акты от исполнителя. Начинается, когда юрист даст текст агентского договора.
3. **Sprint 8** — чеки через «Мой налог». Можно параллельно со Sprint 7.
4. **Sprint 9** — номинальный счёт и защита предоплаты. Начинается после ответа банков.
5. **Sprint 10** — интеграция со страховой. После договора со страховой.

Переписывание ядра и учёт авторства идут параллельно всем спринтам.

### Внешние блокеры

| Кто | Что нужно | Что блокирует |
|-----|-----------|---------------|
| Юрист | Текст агентского договора «от имени и за счёт исполнителя»; не делает ли фиксированная цена платформу исполнителем; лимит выплат по страховке в B2C; регресс к мастеру при подмене; нужен ли статус страхового агента; допустима ли заранее отмеченная галочка страховки (ЗоЗПП ст. 16) | Sprint 7, Sprint 10 |
| Юрист по IP | Условия внесения переписанного ядра и своих модулей в реестр российского ПО | Подача в реестр |
| Бухгалтер | Учёт по агентской схеме (доход = комиссия, раздельный учёт транзитных денег); как выдают чеки ИП и ООО (не НПД) | Sprint 8, Sprint 9 |
| 2–3 банка | Номинальный счёт для маркетплейса услуг: бенефициары НПД/ИП/ООО, API автовыплат, стыковка с эквайрингом, автоудержание комиссии, возвраты, тарифы | Sprint 9 |
| Страховые | Условия полиса и API | Sprint 10 |

### Sprint 6 — без внешних блокеров

**Статус: в работе 🔄** (ветка `claude/sprint6-work`)

| Задача | Статус | Где |
|--------|--------|-----|
| Показывать исполнителя клиенту до оплаты: ФИО или название, статус (НПД/ИП/ООО), ИНН (+ОГРН) | ✅ | `App\Support\ExecutorDisclosure` → поле `executors` в ответе расчёта корзины (`CheckoutTrait`); сайт — корзина (строка под исполнителем) и страница оплаты (блок «Исполнитель услуги»); `fixit_user` — экран оплаты (`ExecutorDisclosureLayout`, нужна пересборка APK). Данные только из проверки ИНН; без неё — «Данные исполнителя ещё не проверены». Тест `ExecutorDisclosureTest` |
| Демо-исполнители без верификации показываются как «не проверены» | ✅ | `DemoVerificationSeeder`: ИНН/ОГРН с несуществующим кодом региона 00 (контрольные цифры верные), пометка «Демо-данные» в `manual_review_reason` |
| Сохранять данные исполнителя в заказе на момент оплаты (снимок для договора) | ✅ | `bookings.executor_snapshot` (JSON), заполняется в `Booking::saving` при появлении исполнителя; документы Sprint 7 берут данные отсюда |
| Тексты без обещаний «мы гарантируем» и без позиционирования платформы как исполнителя | ✅ | `ThemeOptionSeeder`: «О нас» — платформа помогает найти исполнителя, «проверенные» → «с проверкой ИНН», «в команде» → «на платформе». Выдуманные цифры шаблона (3,5 года, 520 отзывов, 10 000 клиентов, 60 исполнителей) убраны, блок выключен до реальных данных |
| Обновить тексты «О нас» и SEO в уже работающей БД | ❌ | Сидер не обновит существующую запись (`updateOrCreate` по всему `options` создаст вторую строку, а сайт читает первую) — поправить в админке: Тема → О нас / SEO |
| Опция «застраховать услугу»: поле в заказе, галочка при оформлении, включение и цена — в админке (по умолчанию выключено, пока нет страховой). Покрываются только работы, оформленные на платформе | ✅ | `App\Support\ServiceInsurance`; админка: Настройки → Общие → «Страхование услуги» (вкл., фикс/процент, цена, страховая, ссылка на условия); миграция `bookings`: `is_insured`, `insurance_amount`, `insurance_insurer` (снимок); сумма добавляется к итогу до расчёта предоплаты; сайт — галочка в корзине и строка в счёте; `fixit_user` — `InsuranceOptionLayout` в корзине (нужна пересборка APK); карточка заказа в админке. Тест `ServiceInsuranceTest`; сквозной расчёт на демо-услуге: 7510 → 7709 ₽ при 199 ₽ |
| Галочка «Застраховать услугу» отмечена по умолчанию (решение 13.10.2026) | ✅ | Настройка «Галочка отмечена по умолчанию» (вкл.); снятая клиентом галочка запоминается. ⚠️ Проверить у юриста: ЗоЗПП ст. 16 о заранее проставленном согласии на доп. платные услуги — при риске выключить в админке |
| Повтор застрахованного заказа тоже застрахован (решение 13.10.2026) | ✅ | `RecurringBookingRepository` пишет `insurance` в шаблон, `GenerateRecurringBookings` передаёт его (для старых шаблонов — по исходному заказу) |
| Миграцию страховки применить к рабочей БД (`php artisan migrate`) | ❌ | При вливании ветки в `main` — после бэкапа в `db_backups/` |
| Страховка для повторяющихся (scheduled) заказов | ✅ | Сумма страховки серии передаётся в `createScheduledParentBooking` сервером (`insurance_snapshot`) и входит в итог и снимок родителя |
| Инвентаризация кода: что осталось от Fixit/CodeCanyon, что уже своё — основа для реестра ПО и плана переписывания ядра | ✅ | [`docs/code-inventory.md`](../docs/code-inventory.md): своего кода ≈3%, 17 выключенных модулей (≈9,8 тыс. строк) можно удалить |
| Учёт авторства: договоры с каждым разработчиком об отчуждении исключительных прав ООО, история коммитов | ❌ | Документы ООО |
| Секреты не уходят в публичный `/api/settings` (`agora.certificate`, `general.firebase_server_key`, `firebase.service_json`) | ✅ | `SettingController::SECRET_KEYS`, тест `SecretsExposureTest` |
| Сервисный ключ Firebase перенесён из `public/admin/assets/firebase.json` (был доступен по URL) в `storage/app/private/firebase/` | ✅ | `Helpers::getFirebaseServiceAccount()` переносит старый файл сам при первом обращении; в таблице настроек — только `project_id` и `client_email` |
| Push из приложений через бэкенд `POST /api/push/send` вместо подписи FCM ключом на телефоне | ✅ | `PushNotificationController`; приложения — `FirebaseApi.sendNotification` (нужна пересборка APK) |
| Звонки клиент ↔ исполнитель через свой сервер вместо Agora (решение 13.10.2026) | ✅ | Свой LiveKit (`infra/livekit`, Docker); бэкенд: `App\Support\Calls`, `CallController` (`/api/calls`: позвонить, принять, отклонить, завершить, входящий), таблица `calls`; звонить можно только участнику общего заказа (самозанятый отвечает и за своего теневого мастера); JWT LiveKit подписывает сервер, ключи — админка: Настройки → Сторонние сервисы → «Звонки», в `/api/settings` уходит только «включено»; оба приложения — `lib/calls/` (аудио/видео, микрофон, динамик, камера), входящий — по push и при открытии приложения. Agora и его мёртвый код удалены. Тест `CallsTest` |
| Звонки: полноэкранный входящий вызов на заблокированном телефоне (CallKit / ConnectionService) | ❌ | Сейчас входящий приходит уведомлением; экран вызова — при открытии приложения |
| Звонки: сервер LiveKit в проде — домен с TLS (`wss://`), открыть UDP 50000–50100, при строгих NAT — TURN | ❌ | Для теста работает в локальной сети `ws://192.168.1.127:7880` |
| Перевыпустить сервисный ключ Firebase перед выходом в интернет | ❌ | Старый ключ лежал в `public/` и был доступен в локальной сети |
| Ключи ЮКассы, CloudPayments, SMS.ru, SMSC — из админки («Платёжные методы», «SMS-шлюзы»), а не `env()` | ✅ | `App\Support\GatewayCredentials`: зашифровано в `integration_settings` (`gateway.<ключ>`), `.env` — запасной вариант; админка больше не пишет ключи в `.env` и в `Modules/*/Config/payment.php`. Тест `GatewayCredentialsTest` |
| Удалить 17 выключенных модулей Fixit (Stripe, PayPal, RazorPay, Twilio и др., ≈9,8 тыс. строк) | ✅ | Удалены вместе с публичными файлами и 4 composer-пакетами (2026-10-13) |
| Логотипы ЮКассы, CloudPayments, SMS.ru, SMSC в админке не грузятся | ❌ | Нет `public/modules/<имя>/images/logo.svg` — добавить файлы |

### Sprint 7 — договор и акты от исполнителя

**Статус: ⚠️ ждёт юриста**

| Задача | Статус | Где |
|--------|--------|-----|
| Агентский договор B2C «от имени и за счёт исполнителя» вместо «от своего имени»: новые версии шаблонов, переподписание действующими исполнителями по SMS | ⚠️ | `resources/views/contracts/`, `ContractService`, версии в `integration_settings` |
| Оферта для клиента: договор на услугу заключается с исполнителем, платформа — посредник | ⚠️ | Страницы оферты и оформления заказа |
| Акты B2C формирует исполнитель, а не платформа | ⚠️ | Генерация документов по заказу |

### Sprint 8 — чеки

**Статус: ⚠️ частично ждёт бухгалтера**

| Задача | Статус | Где |
|--------|--------|-----|
| Чек на услугу от самозанятого через API «Мой налог». Ключи и режим — только через админку | ❌ | Новый модуль, настройки в админке |
| Чеки ИП и ООО — схема по ответу бухгалтера (их касса или иной механизм) | ⚠️ | — |
| Чек платформы только на комиссию, если она оплачивается отдельно | ⚠️ | Онлайн-касса платформы |

### Sprint 9 — номинальный счёт и защита предоплаты

**Статус: ⚠️ ждёт банков**

| Задача | Статус | Где |
|--------|--------|-----|
| Выбрать банк по итогам разговоров с 2–3 банками. Предпочтение — номинальный счёт, а не двухэтапное удержание на карте | ⚠️ | — |
| Деньги клиента держатся на номинальном счёте отдельно от денег ООО; выплата исполнителю после выполнения услуги, комиссия удерживается автоматически | ⚠️ | Платёжный модуль (проектировать после выбора банка), YooKassa/CloudPayments |
| Возврат клиенту, если услуга не оказана | ⚠️ | Отмена заказа, платёжный модуль |

### Sprint 10 — страховка

**Статус: ⚠️ ждёт страховую**

| Задача | Статус | Где |
|--------|--------|-----|
| Интеграция со страховой: оформление полиса по галочке из Sprint 6, текст «услуга застрахована в [страховая] по условиям полиса» | ⚠️ | Новый модуль, ключи в админке |
| Страховые случаи: только работы, оформленные на платформе; подмена мастера без учёта — ответственность самозанятого, принявшего заказ; работа через агентство (юрлицо) — ответственность агентства | ⚠️ | Правила в оферте и полисе |

### Параллельно всем спринтам

| Задача | Статус | Примечание |
|--------|--------|------------|
| Переписать ядро: заменить код Fixit/CodeCanyon собственным | 🔄 | Уже в работе, в этом репозитории |
| Внести платформу в реестр российского ПО | ❌ | После переписывания ядра; даёт IT-аккредитацию и ставку УСН 1% в ЛО |

B2B не меняется: договорная модель с ограничением ответственности, бригадами и ответственностью юрлица-партнёра.

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
