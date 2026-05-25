<?php
namespace Modules\Smsc\Providers;
use Illuminate\Support\ServiceProvider;
class SmscServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Smsc'; protected string $moduleNameLower = 'smsc';
    public function boot(): void { $this->registerConfig(); $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations')); }
    public function register(): void { $this->app->register(RouteServiceProvider::class); }
    protected function registerConfig(): void {
        $this->publishes([module_path($this->moduleName, 'Config/config.php') => config_path($this->moduleNameLower . '.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/config.php'), $this->moduleNameLower);
        $this->mergeConfigFrom(module_path($this->moduleName, 'Config/sms.php'), $this->moduleNameLower);
    }
}
