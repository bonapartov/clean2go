<?php

namespace App\Http\Middleware;

use App\Helpers\Helpers;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('locale')) {
            app()->setLocale(Session::get('locale'));
        } elseif ($request->hasHeader("Accept-Lang")) {
            app()->setLocale($request->header("Accept-Lang"));
        } else {
            // Session is empty (e.g. after server restart) — load default locale
            // from DB settings instead of falling back to config/app.php ('en').
            try {
                $dbLocale = Helpers::getDefaultLanguageLocale();
                if ($dbLocale) {
                    Session::put('locale', $dbLocale);
                    app()->setLocale($dbLocale);
                } else {
                    app()->setLocale(app()->getLocale());
                }
            } catch (\Throwable $e) {
                app()->setLocale(app()->getLocale());
            }
        }

        return $next($request);
    }
}
