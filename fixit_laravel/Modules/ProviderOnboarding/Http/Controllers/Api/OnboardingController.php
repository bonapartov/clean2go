<?php

namespace Modules\ProviderOnboarding\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\ProviderOnboarding\Models\OnboardingLog;
use Modules\ProviderOnboarding\Models\ProviderVerification;
use Modules\ProviderOnboarding\Services\InnVerificationService;

class OnboardingController extends Controller
{
    public function __construct(private InnVerificationService $innService) {}

    // Шаг 1: проверка ИНН
    public function checkInn(Request $request): JsonResponse
    {
        $request->validate([
            'inn' => ['required', 'string', 'regex:/^\d{10}$|^\d{12}$/'],
        ]);

        $user = $request->user();
        $inn = $request->input('inn');

        OnboardingLog::record($user->id, 'inn_check', 'started', ['inn' => $inn], $request->ip());

        $result = $this->innService->verify($inn);

        if (isset($result['error'])) {
            OnboardingLog::record($user->id, 'inn_check', 'failed', ['error' => $result['error']], $request->ip());
            return response()->json($result, 422);
        }

        $verification = ProviderVerification::updateOrCreate(
            ['user_id' => $user->id],
            [
                'inn' => $inn,
                'taxpayer_type' => $result['taxpayer_type'],
                'legal_name' => $result['legal_name'],
                'npd_status' => $result['npd_status'] ?? null,
                'okveds' => $result['okveds'] ?? null,
                'inn_status' => 'active',
                'inn_verified_at' => now(),
                'onboarding_status' => 'in_progress',
            ]
        );

        $user->onboarding_step = 1;
        $user->save();

        OnboardingLog::record($user->id, 'inn_check', 'completed', [
            'taxpayer_type' => $result['taxpayer_type'],
        ], $request->ip());

        return response()->json($result);
    }

    // Шаг 6: специализация и зона работы
    public function saveSpecialization(Request $request): JsonResponse
    {
        $request->validate([
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'work_zone' => ['nullable', 'array'],
        ]);

        $user = $request->user();

        // Sprint 1: сохраняем в лог, реальная запись в категории — Sprint 2
        OnboardingLog::record($user->id, 'specialization', 'completed', [
            'category_ids' => $request->input('category_ids'),
            'work_zone' => $request->input('work_zone'),
        ], $request->ip());

        $user->onboarding_step = 6;
        $user->save();

        return response()->json([
            'message' => 'Специализация сохранена.',
            'next_step' => 7,
        ]);
    }

    // Шаг 7: финальная активация
    public function complete(Request $request): JsonResponse
    {
        $user = $request->user();

        $verification = ProviderVerification::where('user_id', $user->id)->first();

        if (!$verification || $verification->onboarding_status !== 'approved') {
            return response()->json([
                'error' => 'onboarding_not_approved',
                'message' => 'Верификация ещё не завершена.',
                'current_status' => $verification?->onboarding_status,
            ], 422);
        }

        $user->onboarding_completed = true;
        $user->onboarding_step = 7;
        $user->save();

        $verification->update(['onboarding_status' => 'approved']);

        OnboardingLog::record($user->id, 'complete', 'completed', [], $request->ip());

        return response()->json([
            'message' => 'Верификация пройдена. Добро пожаловать!',
            'onboarding_completed' => true,
        ]);
    }

    // Статус онбординга (Flutter polling)
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $verification = ProviderVerification::where('user_id', $user->id)->first();

        return response()->json([
            'onboarding_completed' => $user->onboarding_completed,
            'onboarding_step' => $user->onboarding_step,
            'onboarding_blocked' => $user->onboarding_blocked,
            'taxpayer_type' => $verification?->taxpayer_type,
            'onboarding_status' => $verification?->onboarding_status,
            'passport_status' => $verification?->passport_status,
            'fssp_status' => $verification?->fssp_status,
        ]);
    }
}
