<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventRequestsDuringDemo
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if ($request->isMethod('post') || $request->isMethod('put') || $request->isMethod('delete')) {

            if (config('app.demo')) {

                return back()->with('error', 'Oops! This action disabled in demo mode.');
            }
        }

        return $next($request);
    }
}
