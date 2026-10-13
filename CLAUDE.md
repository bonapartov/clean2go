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

**SMS flow**: `App\SMS\SMS` dispatches through `MessageTrait`. SMS module `getIntent($sendTo, $message)` must accept both string and array message. Keys (`smsru_api_key`, `smsru_sender`, `smsc_login`, `smsc_password`, `smsc_sender`) are entered in admin «SMS-шлюзы» and read with `App\Support\GatewayCredentials::get()`; `.env` (`SMSRU_API_KEY`, …) is only a fallback.

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

## Calls (own LiveKit server)

App-to-app calls (client ↔ executor) run on a self-hosted LiveKit server, not
Agora: `infra/livekit/` (`docker compose up -d`, keys in gitignored `.env`).
Backend: `App\Support\Calls`, `CallController` (`/api/calls/*`), table `calls`;
only booking participants may call each other. The same key/secret pair goes
into admin «Сторонние сервисы → Звонки». Apps: identical `lib/calls/` in both.

## Flutter Apps (fixit_user / fixit_provider)

### Commands

```bash
flutter pub get
flutter run
flutter build apk --release --target-platform android-arm64 \
  --dart-define=BACKEND_URL=http://192.168.1.127:8001   # backend URL is build-time only
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

Build one app at a time and run `./gradlew --stop` in `android/` afterwards: this
machine has 7.7 GB RAM and a release build plus a leftover Gradle daemon gets
killed for low memory. Kotlin compiles in-process (`gradle.properties`) for the
same reason. Firebase config is read from `google-services.json` via
`Firebase.initializeApp()` with no options.

API base URL and app-wide constants are in `lib/config.dart`. Firebase config is per-platform (`google-services.json` / `GoogleService-Info.plist`).

The user app supports multiple home layout themes (Berlin, Dubai, Tokyo, Toronto, New York) selectable from backend settings. Layout files are under `screens/bottom_screens/home_screen/<city>_layout/`.

### State management

Uses `provider` package with `ChangeNotifier`. Providers are registered in `lib/providers/index.dart` and consumed via `context.watch<T>()` / `context.read<T>()`.

## Sensitive Settings: Admin Panel, Never Hardcoded

All sensitive or environment-specific settings (API keys, secrets, logins,
sender names, shop IDs, webhook secrets, service URLs, mock/live switches)
must be editable from the admin panel. Never hardcode them in PHP, Blade,
Dart, JS or seeders, and never commit real values.

- Store them where the admin can change them: `integration_settings` (via
  `IntegrationSetting::get()/set()`, encrypted) or the existing admin
  settings/payment-method/SMS-method config screens. `.env` is only a
  fallback/default, not the place the operator is expected to edit.
- Code reads the setting through that layer, not `env()` directly
  (`env()` returns null once config is cached).
- When adding an integration, add its fields to the admin UI in the same
  change. A feature that requires editing code or `.env` to configure is
  not finished.
- Placeholders are fine; fabricated values are not. If a real key is missing,
  leave the field empty and say so — don't invent one.
- Public endpoints (`/api/settings` is unauthenticated) must not return
  secrets: list them in `SettingController::SECRET_KEYS`. Never put key
  files under `public/` — it is web-served. The Firebase service account
  lives in `storage/app/private/firebase/` (`Helpers::getFirebaseServiceAccount()`);
  apps send push via `POST /api/push/send`, never with a key of their own.
- Flutter apps can't read the admin panel before they know where the backend
  is, so the backend URL is the one exception: pass it at build time
  (`--dart-define=API_URL=...`), never hardcode an IP/host in `lib/`.
  Everything else the apps need comes from the backend settings API.

## Adding a New Payment Module

1. Copy an existing Russian module (e.g. `Modules/YooKassa/`) as a template
2. Implement `Payment/<Name>.php` with static `getIntent()` and `webhook()` methods
3. Register in `Providers/<Name>ServiceProvider.php`
4. Add module to `modules_statuses.json` with `true`
5. List its keys in `Config/payment.php` `'configs'` with blank values and in `'fields'` — they appear in admin «Платёжные методы» automatically; read them with `GatewayCredentials::get('<field key>')`, never `env()` (see "Sensitive Settings")
6. Wire webhook route in `Routes/api.php`

## Adding a New SMS Module

1. Copy `Modules/Smsru/` as template
2. Implement `SMS/<Name>.php` with static `getIntent($sendTo, $message)` — handle both string and array `$message`
3. Register provider and enable in `modules_statuses.json`
4. Keys: blank `'configs'` + `'fields'` in `Config/sms.php`, read with `GatewayCredentials::get()` (admin «SMS-шлюзы»)

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

## Git Workflow

`main` is the trunk and must always reflect current, working state — it is not
a branch that sits untouched while real development happens elsewhere for
weeks. As of 2026-09-16, `main` had drifted to the very first commit
("Initial commit: Fixit → Clean2Go") while 96 real commits accumulated on a
single long-running branch across many sessions; that branch was fast-forward
merged into `main` to fix this. Don't let it happen again:

- **One branch per task.** Start a new branch for each distinct piece of work
  (a feature, a bug fix, a batch of related fixes done in one sitting). Do
  not keep adding unrelated work to a branch that already merged-worthy —
  cut a new branch instead, even mid-session if the topic changes.
- **Merge promptly.** Once a branch's work is verified (tested, reviewed),
  merge it into `main` and move on. Don't let finished work sit unmerged
  "just in case" — that's how `main` goes stale.
- **Merge mechanics:**
  - If the branch has any real divergence from `main` (i.e.
    `git merge-base --is-ancestor main <branch>` is false), merge via PR —
    `gh pr create`, review the diff, `gh pr merge`. Never force-push over
    divergent history on `main`.
  - If the branch is a pure fast-forward (branched from `main`'s current tip,
    zero commits unique to `main`), a direct
    `git push origin <branch>:main` is fine — it's mechanically identical to
    a fast-forward PR merge, just without the GitHub UI wrapper. This is the
    common case for Claude Code's own auto-created `claude/*` session
    branches.
  - `gh` CLI is installed (`~/.local/bin/gh`) but **not authenticated** as
    of 2026-10-13. Run `gh auth login` before relying on the PR-based path
    above for a branch with real divergence; until then, use the GitHub web
    UI for anything that isn't a clean fast-forward.
- **Clean up after merging.** Delete a branch once it's merged into `main` —
  except never delete the branch a running session currently has checked out.
- Claude Code's auto-generated branch names (`claude/<slug>-<id>`) are fine to
  keep using — the naming isn't the problem, letting one branch live for many
  unrelated sessions is.
