<?php

namespace App\Providers;


use App\Helpers\Helpers;
use Database\Seeders\ThemeOptionSeeder;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Spatie\Translatable\Facades\Translatable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Онбординг: проверка ИНН — 5 попыток в час с одного IP
        RateLimiter::for('onboarding-inn', function (Request $request) {
            return Limit::perHour(5)->by($request->ip());
        });

        // Онбординг: отправка SMS — 3 в час на пользователя
        RateLimiter::for('onboarding-sms', function (Request $request) {
            return Limit::perHour(3)->by($request->user()?->id ?: $request->ip());
        });

        // Онбординг: подписание договора — 3 попытки в 15 минут на пользователя
        RateLimiter::for('onboarding-sign', function (Request $request) {
            return Limit::perMinutes(15, 3)->by($request->user()?->id ?: $request->ip());
        });

        Collection::macro('paginate', function ($perPage = 15) {
            $page = LengthAwarePaginator::resolveCurrentPage('page');
            return new LengthAwarePaginator($this->forPage($page, $perPage), $this->count(), $perPage, $page, [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]);
        });

        Schema::defaultStringLength(191);
        Paginator::useBootstrap();
        if(!request()->wantsJson()){
            $themeOptions = $this->getThemeOptions();
            view()->share('themeOptions', $themeOptions);
        }
        Translatable::fallback(fallbackAny: true);
        Model::automaticallyEagerLoadRelationships();
    }

    private function getThemeOptions()
    {
        if ($this->isDatabaseConnected()) {
            try {
                return  Helpers::getThemeOptions();

            } catch (Exception $e) {
                return $this->getDefaultThemeOptions();
            }
        }

        return $this->getDefaultThemeOptions();
    }

    private function isDatabaseConnected()
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function databaseHasTables()
    {
        try {
            return count(Schema::getAllTables()) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getDefaultThemeOptions()
    {
        $themeOptionsSeeder = new ThemeOptionSeeder();
        return $themeOptionsSeeder->getThemeOptions();
    }
}
