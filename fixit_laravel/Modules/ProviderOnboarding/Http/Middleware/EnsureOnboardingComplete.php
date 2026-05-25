<?php

namespace Modules\ProviderOnboarding\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'unauthenticated'], 401);
        }

        if (!$user->hasAnyRole(['provider', 'serviceman'])) {
            return $next($request);
        }

        if ($user->onboarding_blocked) {
            return response()->json([
                'error' => 'onboarding_blocked',
                'message' => $user->onboarding_blocked_reason ?? 'Ваш аккаунт заблокирован. Обратитесь в поддержку.',
            ], 403);
        }

        if (!$user->onboarding_completed) {
            return response()->json([
                'error' => 'onboarding_required',
                'message' => 'Необходимо пройти верификацию.',
                'current_step' => $user->onboarding_step,
                'redirect' => '/onboarding',
            ], 403);
        }

        return $next($request);
    }
}
