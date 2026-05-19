<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\RoleEnum;

class CheckZonePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();
        
        // If no user is authenticated, continue (let auth middleware handle it)
        if (!$user) {
            return $next($request);
        }
        
        // Admin users can access any zone
        if ($user->hasRole(RoleEnum::ADMIN)) {
            return $next($request);
        }
        
        // Get zone_id from request (query parameter or request data)
        $zoneId = $request->get('zone_id') ?? $request->input('zone_id');
        
        // If no zone_id in request, allow (optional filtering)
        if (!$zoneId) {
            return $next($request);
        }
        
        // Check if user has permission for the requested zone
        if (!$user->hasZonePermission($zoneId)) {
            return redirect()->back()
                ->with('error', __('You don\'t have permission to access this zone.'));
        }
        
        return $next($request);
    }
}

