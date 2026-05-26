<?php

namespace Modules\ProviderOnboarding\Jobs;

use App\Helpers\Helpers;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\ProviderOnboarding\Events\OnboardingStepCompleted;
use Modules\ProviderOnboarding\Listeners\NotifyAdminOnManualReview;
use Modules\ProviderOnboarding\Models\OnboardingLog;
use Modules\ProviderOnboarding\Models\ProviderVerification;
use Modules\ProviderOnboarding\Services\PassportVerificationService;

class VerifyPassportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    private const PUSH_MESSAGES = [
        'approved'      => ['title' => 'Паспорт подтверждён ✓',  'body' => 'Ваш паспорт успешно верифицирован. Можно продолжить оформление.'],
        'rejected'      => ['title' => 'Паспорт отклонён',       'body' => 'Ваши документы не прошли проверку. Войдите в приложение для уточнения причины.'],
        'manual_review' => ['title' => 'Документы на проверке',  'body' => 'Ваши документы переданы администратору. Мы уведомим вас о результате.'],
        'failed'        => ['title' => 'Ошибка верификации',     'body' => 'Не удалось проверить документы. Попробуйте загрузить их повторно.'],
    ];

    public function __construct(
        private readonly int $userId,
        private readonly int $verificationId,
    ) {
        $this->onQueue('passport');
    }

    public function handle(PassportVerificationService $service): void
    {
        $verification = ProviderVerification::findOrFail($this->verificationId);
        $user = User::findOrFail($this->userId);

        $result = $service->verify($verification);

        $verification->update([
            'passport_status'       => $result['status'],
            'passport_verified_at'  => now(),
            'passport_raw_response' => $result['raw_response'],
        ]);

        OnboardingLog::record($user->id, 'passport_verification', $result['status'], [
            'provider' => $result['raw_response']['provider'] ?? 'unknown',
            'message'  => $result['message'],
        ]);

        if ($result['status'] === 'manual_review') {
            (new NotifyAdminOnManualReview())->handle(
                new OnboardingStepCompleted($user, 3, 'manual_review')
            );
        }

        $this->sendPush($user->id, $result['status']);

        OnboardingStepCompleted::dispatch($user, 3, $result['status']);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('VerifyPassportJob failed', [
            'user_id'         => $this->userId,
            'verification_id' => $this->verificationId,
            'error'           => $e->getMessage(),
        ]);

        ProviderVerification::where('id', $this->verificationId)->update([
            'passport_status' => 'failed',
        ]);

        $this->sendPush($this->userId, 'failed');
    }

    private function sendPush(int $userId, string $status): void
    {
        $msg = self::PUSH_MESSAGES[$status] ?? self::PUSH_MESSAGES['failed'];

        $payload = [
            'message' => [
                'topic'        => 'user_' . $userId,
                'notification' => [
                    'title' => $msg['title'],
                    'body'  => $msg['body'],
                ],
                'data' => [
                    'type'             => 'passport_verification',
                    'passport_status'  => $status,
                ],
            ],
        ];

        try {
            Helpers::pushNotification($payload);
        } catch (\Throwable $e) {
            Log::error('VerifyPassportJob: push failed', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
