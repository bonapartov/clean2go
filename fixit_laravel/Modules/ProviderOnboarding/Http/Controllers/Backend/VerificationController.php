<?php

namespace Modules\ProviderOnboarding\Http\Controllers\Backend;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\ProviderOnboarding\Models\OnboardingLog;
use Modules\ProviderOnboarding\Models\ProviderVerification;

class VerificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = ProviderVerification::with('user')
            ->when($request->input('status'), fn($q, $v) => $q->where('onboarding_status', $v))
            ->when($request->input('type'), fn($q, $v) => $q->where('taxpayer_type', $v))
            ->latest();

        $verifications = $query->paginate(20);

        return view('backend.onboarding.verifications.index', compact('verifications'));
    }

    public function show(int $id): View
    {
        $verification = ProviderVerification::with(['user', 'reviewer'])->findOrFail($id);
        $logs = OnboardingLog::where('user_id', $verification->user_id)->latest('created_at')->get();

        return view('backend.onboarding.verifications.show', compact('verification', 'logs'));
    }

    public function approve(int $id): RedirectResponse
    {
        $verification = ProviderVerification::findOrFail($id);

        // Снимает ручную проверку (например, документов ООО) и пускает дальше по шагам.
        // 'approved' — терминальный статус, его выставляет только signContract() после
        // реального подписания договора; здесь его ставить нельзя, иначе complete()
        // и подписание договора можно обойти одним кликом админа до паспорта/договора.
        $verification->update([
            'onboarding_status' => 'in_progress',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        OnboardingLog::record($verification->user_id, 'admin_review', 'approved', [
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('backend.verifications.show', $id)
            ->with('success', 'Заявка одобрена, исполнитель может продолжить оформление.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:10']]);

        $verification = ProviderVerification::findOrFail($id);
        $verification->update([
            'onboarding_status' => 'rejected',
            'rejection_reason' => $request->input('reason'),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $verification->user->update(['onboarding_blocked' => true, 'onboarding_blocked_reason' => $request->input('reason')]);

        OnboardingLog::record($verification->user_id, 'admin_review', 'rejected', [
            'reason' => $request->input('reason'),
            'admin_id' => auth()->id(),
        ]);

        return redirect()->route('backend.verifications.show', $id)
            ->with('success', 'Заявка отклонена.');
    }

    public function requestDocs(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:10']]);

        $verification = ProviderVerification::findOrFail($id);
        $verification->update([
            'manual_review_reason' => $request->input('reason'),
            'onboarding_status' => 'pending_manual',
        ]);

        OnboardingLog::record($verification->user_id, 'admin_review', 'request_docs', [
            'reason' => $request->input('reason'),
        ]);

        return redirect()->route('backend.verifications.show', $id)
            ->with('success', 'Запрос на доп. документы отправлен.');
    }

    // Отдельное от общего "Одобрить заявку" действие: подтверждает именно паспорт.
    // ManualPassportProvider всегда возвращает manual_review — единственный способ
    // передвинуть passport_status в 'verified' сейчас — это ручное решение здесь.
    public function approvePassport(int $id): RedirectResponse
    {
        $verification = ProviderVerification::findOrFail($id);

        $verification->update([
            'passport_status' => 'verified',
            'passport_verified_at' => now(),
        ]);

        OnboardingLog::record($verification->user_id, 'passport_verification', 'admin_approved', [
            'admin_id' => auth()->id(),
        ]);

        $this->notifyPassportStatus($verification->user_id, 'verified');

        return redirect()->route('backend.verifications.show', $id)
            ->with('success', 'Паспорт подтверждён.');
    }

    public function rejectPassport(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:10']]);

        $verification = ProviderVerification::findOrFail($id);

        $verification->update([
            'passport_status' => 'rejected',
            'passport_verified_at' => now(),
        ]);

        OnboardingLog::record($verification->user_id, 'passport_verification', 'admin_rejected', [
            'reason' => $request->input('reason'),
            'admin_id' => auth()->id(),
        ]);

        $this->notifyPassportStatus($verification->user_id, 'rejected');

        return redirect()->route('backend.verifications.show', $id)
            ->with('success', 'Паспорт отклонён.');
    }

    private function notifyPassportStatus(int $userId, string $status): void
    {
        $messages = [
            'verified' => ['title' => 'Паспорт подтверждён ✓', 'body' => 'Ваш паспорт успешно верифицирован. Можно продолжить оформление.'],
            'rejected' => ['title' => 'Паспорт отклонён', 'body' => 'Ваши документы не прошли проверку. Войдите в приложение для уточнения причины.'],
        ];

        $msg = $messages[$status] ?? null;
        if (!$msg) {
            return;
        }

        try {
            Helpers::pushNotification([
                'message' => [
                    'topic' => 'user_' . $userId,
                    'notification' => ['title' => $msg['title'], 'body' => $msg['body'], 'image' => ''],
                    'data' => ['type' => 'passport_verification', 'passport_status' => $status],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('VerificationController: passport push failed', ['user_id' => $userId, 'error' => $e->getMessage()]);
        }
    }
}
