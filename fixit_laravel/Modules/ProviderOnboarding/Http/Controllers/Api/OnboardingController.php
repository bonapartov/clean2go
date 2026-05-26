<?php

namespace Modules\ProviderOnboarding\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\ProviderOnboarding\Models\IntegrationSetting;
use Modules\ProviderOnboarding\Models\OnboardingLog;
use Modules\ProviderOnboarding\Models\ProviderVerification;
use Modules\ProviderOnboarding\Services\ContractService;
use Modules\ProviderOnboarding\Services\InnVerificationService;
use Modules\ProviderOnboarding\Services\NpdVerificationService;

class OnboardingController extends Controller
{
    public function __construct(
        private InnVerificationService $innService,
        private NpdVerificationService $npdService,
        private ContractService $contractService,
    ) {}

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

        // Для физлиц и самозанятых сразу проверяем НПД
        $npdStatus = $result['npd_status'] ?? null;
        if (in_array($result['taxpayer_type'], ['self_employed', 'ip_on_npd']) && $npdStatus === null) {
            $npd = $this->npdService->check($inn);
            $npdStatus = $npd['status'];
        }

        $okveds = $result['okveds'] ?? null;
        $okvedWarning = in_array($result['taxpayer_type'], ['individual_entrepreneur', 'ip_on_npd'])
            && $this->innService->hasNpdIncompatibleOkveds($okveds);

        $verification = ProviderVerification::updateOrCreate(
            ['user_id' => $user->id],
            [
                'inn' => $inn,
                'taxpayer_type' => $result['taxpayer_type'],
                'legal_name' => $result['legal_name'],
                'npd_status' => $npdStatus,
                'npd_verified_at' => $npdStatus !== 'pending' ? now() : null,
                'npd_raw_response' => $result['raw']['npd'] ?? null,
                'okveds' => $okveds,
                'okved_warning' => $okvedWarning,
                'ogrn' => $result['ogrn'] ?? null,
                'registration_date' => $result['registration_date'] ?? null,
                'director_name' => $result['director_name'] ?? null,
                'inn_status' => 'active',
                'inn_verified_at' => now(),
                'inn_raw_response' => isset($result['raw']) ? $result['raw'] : null,
                'onboarding_status' => 'in_progress',
            ]
        );

        $user->onboarding_step = 1;
        $user->save();

        OnboardingLog::record($user->id, 'inn_check', 'completed', [
            'taxpayer_type' => $result['taxpayer_type'],
            'npd_status' => $npdStatus,
        ], $request->ip());

        return response()->json(array_merge($result, [
            'npd_status' => $npdStatus,
            'okved_warning' => $okvedWarning,
            // Шаг 2Б: Flutter показывает экран предупреждения если okved_warning = true и taxpayer_type = ip/ip_on_npd
        ]));
    }

    // Шаг 5a: генерация договора
    public function generateContract(Request $request): JsonResponse
    {
        $user = $request->user();
        $verification = ProviderVerification::where('user_id', $user->id)->firstOrFail();

        ['path' => $path, 'version' => $version] = $this->contractService->generate($verification);

        $verification->update([
            'contract_path' => $path,
            'contract_version' => $version,
            'contract_ip_address' => $request->ip(),
        ]);

        OnboardingLog::record($user->id, 'contract', 'generated', [
            'version' => $version,
            'type' => $verification->contract_type ?? $verification->taxpayer_type,
        ], $request->ip());

        return response()->json([
            'message' => 'Договор сформирован. Ознакомьтесь и подтвердите подписание через SMS.',
            'next_step' => '5b',
        ]);
    }

    // Шаг 5b: отправка SMS OTP
    public function sendContractSms(Request $request): JsonResponse
    {
        $user = $request->user();
        $verification = ProviderVerification::where('user_id', $user->id)->firstOrFail();

        if (!$verification->contract_path) {
            return response()->json(['error' => 'contract_not_generated', 'message' => 'Сначала сгенерируйте договор.'], 422);
        }

        // Throttle: не чаще 1 раза в минуту
        if ($verification->contract_sms_sent_at && $verification->contract_sms_sent_at->diffInSeconds(now()) < 60) {
            return response()->json(['error' => 'too_many_requests', 'message' => 'SMS уже отправлен. Повторите через минуту.'], 429);
        }

        $code = (string) random_int(100000, 999999);

        $verification->update([
            'contract_sms_code' => Hash::make($code),
            'contract_sms_sent_at' => now(),
        ]);

        $phone = $user->phone ?? $user->mobile;
        \App\SMS\SMS::send($phone, "Код подтверждения договора: {$code}. Никому не сообщайте.");

        OnboardingLog::record($user->id, 'contract', 'sms_sent', [], $request->ip());

        return response()->json(['message' => 'SMS с кодом подтверждения отправлен.', 'next_step' => '5c']);
    }

    // Шаг 5c: подписание договора по SMS OTP
    public function signContract(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $user = $request->user();
        $verification = ProviderVerification::where('user_id', $user->id)->firstOrFail();

        if (!$verification->contract_sms_code || !$verification->contract_sms_sent_at) {
            return response()->json(['error' => 'no_sms_sent', 'message' => 'Сначала запросите SMS с кодом.'], 422);
        }

        // Код действителен 15 минут
        if ($verification->contract_sms_sent_at->diffInMinutes(now()) > 15) {
            return response()->json(['error' => 'code_expired', 'message' => 'Срок действия кода истёк. Запросите новый SMS.'], 422);
        }

        if (!Hash::check($request->input('code'), $verification->contract_sms_code)) {
            OnboardingLog::record($user->id, 'contract', 'sign_failed', ['reason' => 'wrong_code'], $request->ip());
            return response()->json(['error' => 'wrong_code', 'message' => 'Неверный код подтверждения.'], 422);
        }

        $contractType = $verification->contract_type
            ?? match ($verification->taxpayer_type) {
                'self_employed' => 'self_employed',
                'individual_entrepreneur', 'ip_on_npd' => 'ip',
                'legal_entity' => 'ooo',
                default => 'self_employed',
            };

        $verification->update([
            'contract_type' => $contractType,
            'contract_signed_at' => now(),
            'contract_sign_method' => 'sms_otp',
            'contract_sms_code' => null, // invalidate after use
            'onboarding_status' => 'approved',
        ]);

        $user->onboarding_step = 5;
        $user->save();

        OnboardingLog::record($user->id, 'contract', 'signed', ['type' => $contractType], $request->ip());

        return response()->json(['message' => 'Договор подписан.', 'next_step' => 6]);
    }

    // Фиксация потери НПД
    public function npdLost(Request $request): JsonResponse
    {
        $user = $request->user();
        $verification = ProviderVerification::where('user_id', $user->id)->firstOrFail();

        // Актуализируем статус через nalog.ru перед заморозкой
        $npd = $this->npdService->check($verification->inn);

        if ($npd['status'] === 'active') {
            return response()->json([
                'message' => 'Статус НПД активен по данным nalog.ru. Выплаты не заморожены.',
                'npd_status' => 'active',
            ]);
        }

        $verification->update([
            'npd_status' => $npd['status'] === 'pending' ? 'pending' : 'inactive',
            'npd_verified_at' => now(),
            'npd_raw_response' => $npd['raw'],
            'payments_frozen' => true,
            'payments_frozen_reason' => 'npd_lost',
            'payments_frozen_at' => now(),
        ]);

        OnboardingLog::record($user->id, 'npd', 'npd_lost', [
            'previous_contract_type' => $verification->contract_type,
        ], $request->ip());

        return response()->json([
            'message' => 'Статус НПД утрачен. Выплаты приостановлены.',
            'payments_frozen' => true,
            'options' => ['restore_npd', 'become_ip', 'sign_gph'],
        ], 200);
    }

    // Генерация ГПХ при потере НПД
    public function generateGphContract(Request $request): JsonResponse
    {
        $user = $request->user();
        $verification = ProviderVerification::where('user_id', $user->id)->firstOrFail();

        if (!$verification->payments_frozen) {
            return response()->json(['error' => 'not_frozen', 'message' => 'Выплаты не заморожены.'], 422);
        }

        $verification->contract_type = 'gph';
        $verification->save();

        ['path' => $path, 'version' => $version] = $this->contractService->generate($verification);

        $verification->update([
            'contract_path' => $path,
            'contract_version' => $version,
            'contract_ip_address' => $request->ip(),
        ]);

        OnboardingLog::record($user->id, 'contract', 'gph_generated', ['version' => $version], $request->ip());

        return response()->json([
            'message' => 'Договор ГПХ сформирован. Подтвердите подписание через SMS.',
            'next_step' => 'gph_sign',
        ]);
    }

    // Подписание ГПХ договора (снимает заморозку)
    public function signGphContract(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $user = $request->user();
        $verification = ProviderVerification::where('user_id', $user->id)->firstOrFail();

        if (!$verification->payments_frozen || $verification->contract_type !== 'gph') {
            return response()->json(['error' => 'invalid_state', 'message' => 'Договор ГПХ не был сформирован.'], 422);
        }

        if (!$verification->contract_sms_code || !$verification->contract_sms_sent_at) {
            return response()->json(['error' => 'no_sms_sent', 'message' => 'Сначала запросите SMS с кодом.'], 422);
        }

        if ($verification->contract_sms_sent_at->diffInMinutes(now()) > 15) {
            return response()->json(['error' => 'code_expired', 'message' => 'Срок действия кода истёк. Запросите новый SMS.'], 422);
        }

        if (!Hash::check($request->input('code'), $verification->contract_sms_code)) {
            OnboardingLog::record($user->id, 'contract', 'gph_sign_failed', ['reason' => 'wrong_code'], $request->ip());
            return response()->json(['error' => 'wrong_code', 'message' => 'Неверный код подтверждения.'], 422);
        }

        $previousContractType = $verification->getOriginal('contract_type') ?? 'self_employed';

        $verification->update([
            'contract_signed_at' => now(),
            'contract_sign_method' => 'sms_otp',
            'contract_sms_code' => null,
            'payments_frozen' => false,
            'payments_frozen_reason' => null,
            'payments_frozen_at' => null,
        ]);

        OnboardingLog::record($user->id, 'contract', 'gph_signed', [
            'previous_contract_type' => $previousContractType,
            'new_contract_type' => 'gph',
        ], $request->ip());

        return response()->json([
            'message' => 'Договор ГПХ подписан. Выплаты возобновлены.',
            'payments_frozen' => false,
            'contract_type' => 'gph',
        ]);
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

        // Привязываем категории через стандартный механизм проекта
        if (method_exists($user, 'categories')) {
            $user->categories()->sync($request->input('category_ids'));
        }

        OnboardingLog::record($user->id, 'specialization', 'completed', [
            'category_ids' => $request->input('category_ids'),
            'work_zone' => $request->input('work_zone'),
        ], $request->ip());

        $user->onboarding_step = 6;
        $user->save();

        return response()->json(['message' => 'Специализация сохранена.', 'next_step' => 7]);
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

        OnboardingLog::record($user->id, 'complete', 'completed', [], $request->ip());

        return response()->json(['message' => 'Верификация пройдена. Добро пожаловать!', 'onboarding_completed' => true]);
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
            'npd_status' => $verification?->npd_status,
            'payments_frozen' => $verification?->payments_frozen ?? false,
            'contract_type' => $verification?->contract_type,
        ]);
    }
}
