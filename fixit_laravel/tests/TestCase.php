<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Refuse to run against anything but the test database — BEFORE any
     * trait runs. RefreshDatabase wipes the database it is connected to, and
     * it runs inside setUp() (setUpTraits), right after refreshApplication().
     *
     * Incidents: 2026-09-26 a test run wiped the working fixit_data;
     * 2026-10-13 it happened again with `php artisan config:cache` on (cached
     * config ignores phpunit.xml's DB_DATABASE) because the first version of
     * this guard sat in setUp() after parent::setUp(), i.e. after the wipe.
     * Restored from db_backups/. Hence: check here, not in setUp().
     */
    protected function refreshApplication()
    {
        if (file_exists(dirname(__DIR__).'/bootstrap/cache/config.php')) {
            throw new RuntimeException('Config is cached — tests would use the real database. Run `php artisan config:clear` first.');
        }

        parent::refreshApplication();

        $database = $this->app['config']->get('database.connections.'.$this->app['config']->get('database.default').'.database');
        if (! str_ends_with((string) $database, '_testing')) {
            throw new RuntimeException("Tests must run on a *_testing database, got \"$database\".");
        }

        // Never send real push from tests: without the Firebase key
        // Helpers::pushNotification() is a no-op (topics like user_2 may be
        // real people's phones).
        $this->app['config']->set('services.firebase.service_account_path', sys_get_temp_dir().'/clean2go-tests-no-firebase-key.json');
    }
}
