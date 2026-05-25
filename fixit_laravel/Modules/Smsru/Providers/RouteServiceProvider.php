<?php
namespace Modules\Smsru\Providers;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\Smsru\Http\Controllers';
    public function boot(): void { parent::boot(); }
    public function map(): void { $this->mapApiRoutes(); $this->mapWebRoutes(); }
    protected function mapWebRoutes(): void { Route::middleware('web')->namespace($this->moduleNamespace)->group(module_path('Smsru', '/Routes/web.php')); }
    protected function mapApiRoutes(): void { Route::prefix('api')->middleware('api')->namespace($this->moduleNamespace)->group(module_path('Smsru', '/Routes/api.php')); }
}
