<?php

namespace Modules\ProviderOnboarding\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\ProviderOnboarding\Http\Controllers';

    public function boot(): void { parent::boot(); }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapBackendRoutes();
    }

    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->moduleNamespace)
            ->group(module_path('ProviderOnboarding', '/Routes/api.php'));
    }

    protected function mapBackendRoutes(): void
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('ProviderOnboarding', '/Routes/backend.php'));
    }
}
