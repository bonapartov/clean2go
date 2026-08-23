# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Clean2Go** is a Russian-market home services platform adapted from Fixit. It has three components:

- `fixit_laravel/` — Laravel 12 backend (REST API + admin panel)
- `fixit_user/` — Flutter app for end customers
- `fixit_provider/` — Flutter app for service providers

## Backend (fixit_laravel)

### Commands

```bash
# Development
php artisan serve
npm run dev           # Vite dev server for admin/frontend assets

# Database
php artisan migrate
php artisan db:seed

# Code style (Laravel Pint)
./vendor/bin/pint

# Tests
php artisan test
php artisan test --filter TestClassName   # single test
./vendor/bin/phpunit tests/Unit/FooTest.php  # direct phpunit

# Module management
php artisan module:enable CloudPayments
php artisan module:disable Stripe
```

### Architecture

The backend uses [nwidart/laravel-modules](https://nwidart.com/laravel-modules/) for payment and SMS integrations. Each module lives in `Modules/<Name>/` and follows the same internal structure: `Config/`, `Http/`, `Payment/` or `SMS/`, `Providers/`, `Routes/`.

**Active Russian modules** (see `modules_statuses.json`):
- `CloudPayments` — CloudPayments gateway (HMAC webhook verification, RUB by default)
- `YooKassa` — YooKassa gateway (primary Russian payment processor)
- `Smsru` — SMS.ru provider
- `Smsc` — SMSC.ru provider
- `TwoFactor`, `Firebase`, `Coupon`, `Subscription` also enabled

**Disabled global modules**: Stripe, PayPal, RazorPay, Mollie, Twilio, Msg91, etc. Do not re-enable these for the Russian build.

### Key patterns

**Payment flow**: All payment modules implement `getIntent($obj, $request)` and `webhook($request)`. The `PaymentTrait` (`app/Http/Traits/PaymentTrait.php`) dispatches to the correct module via `Module::find($request->payment_method)`. Payment status transitions use `PaymentStatus` enum (`PENDING → COMPLETED/FAILED`).

**SMS flow**: `App\SMS\SMS` dispatches through `MessageTrait`. SMS module `getIntent($sendTo, $message)` must accept both string and array message. Environment keys: `SMSRU_API_KEY`, `SMSRU_SENDER`, `SMSC_LOGIN`, `SMSC_PASSWORD`.

**Repository pattern**: Controllers → Repositories (`app/Repositories/`) → Models. The `prettus/l5-repository` package is used.

**Helpers**: `app/Helpers/Helpers.php` is autoloaded globally. Use `Helpers::getDefaultCurrencyCode()` for currency, `Helpers::roundNumber()` for amounts. Currency defaults to `RUB`.

**Roles**: Managed via `spatie/laravel-permission`. Roles are defined in `App\Enums\RoleEnum`.

**Spatial queries**: `matanyadaev/laravel-eloquent-spatial` for zone/provider location queries.

### Routes

- `routes/api.php` — public and authenticated REST API (Sanctum tokens)
- `routes/backend.php` — admin panel routes
- `routes/web.php` — frontend web routes

### Assets

Vite compiles three entry points:
- `public/admin/scss/admin.scss` — admin panel
- `resources/js/app.js` — shared JS
- `public/frontend/scss/style.scss` — customer frontend

## Flutter Apps (fixit_user / fixit_provider)

### Commands

```bash
flutter pub get
flutter run
flutter build apk --release
flutter build ios --release
flutter test
flutter test test/widget_test.dart   # single test file
```

Both apps have identical `lib/` structure:
- `screens/` — UI screens (auth, home, booking, etc.)
- `providers/` — state management via `provider` package
- `services/` — API client (`api_service.dart`, `api_methods.dart`)
- `models/` — JSON-serializable data models
- `common/` — theme, extensions, session, assets
- `routes/` — named route definitions
- `widgets/` — shared UI components

### Configuration

API base URL and app-wide constants are in `lib/config.dart`. Firebase config is per-platform (`google-services.json` / `GoogleService-Info.plist`).

The user app supports multiple home layout themes (Berlin, Dubai, Tokyo, Toronto, New York) selectable from backend settings. Layout files are under `screens/bottom_screens/home_screen/<city>_layout/`.

### State management

Uses `provider` package with `ChangeNotifier`. Providers are registered in `lib/providers/index.dart` and consumed via `context.watch<T>()` / `context.read<T>()`.

## Adding a New Payment Module

1. Copy an existing Russian module (e.g. `Modules/YooKassa/`) as a template
2. Implement `Payment/<Name>.php` with static `getIntent()` and `webhook()` methods
3. Register in `Providers/<Name>ServiceProvider.php`
4. Add module to `modules_statuses.json` with `true`
5. Add env keys to `.env.example`
6. Wire webhook route in `Routes/api.php`

## Adding a New SMS Module

1. Copy `Modules/Smsru/` as template
2. Implement `SMS/<Name>.php` with static `getIntent($sendTo, $message)` — handle both string and array `$message`
3. Register provider and enable in `modules_statuses.json`

## Skill routing

When the user's request matches an available skill, invoke it via the Skill tool. When in doubt, invoke the skill.

Key routing rules:
- Product ideas/brainstorming → invoke /office-hours
- Strategy/scope → invoke /plan-ceo-review
- Architecture → invoke /plan-eng-review
- Design system/plan review → invoke /design-consultation or /plan-design-review
- Full review pipeline → invoke /autoplan
- Bugs/errors → invoke /investigate
- QA/testing site behavior → invoke /qa or /qa-only
- Code review/diff check → invoke /review
- Visual polish → invoke /design-review
- Ship/deploy/PR → invoke /ship or /land-and-deploy
- Save progress → invoke /context-save
- Resume context → invoke /context-restore
- Author a backlog-ready spec/issue → invoke /spec
