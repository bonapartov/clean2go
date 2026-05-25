<?php

namespace Modules\ProviderOnboarding\Providers;

use Illuminate\Support\ServiceProvider;

class ProviderOnboardingServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'ProviderOnboarding';
    protected string $moduleNameLower = 'provider-onboarding';

    public function boot(): void
    {
        $this->registerConfig();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));
        // Вьюшки хранятся в resources/views/backend/onboarding/ по паттерну проекта
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
