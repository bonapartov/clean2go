<?php

namespace Modules\ProviderOnboarding\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\ProviderOnboarding\Console\Commands\PurgePassportFiles;
use Modules\ProviderOnboarding\Listeners\CheckPendingNpdOnLogin;

class ProviderOnboardingServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'ProviderOnboarding';
    protected string $moduleNameLower = 'provider-onboarding';

    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        Event::listen(Login::class, CheckPendingNpdOnLogin::class);
        $this->commands([PurgePassportFiles::class]);
    }

    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower
        );
    }
}
