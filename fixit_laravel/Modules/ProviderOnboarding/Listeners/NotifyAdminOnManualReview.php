<?php

namespace Modules\ProviderOnboarding\Listeners;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\ProviderOnboarding\Events\OnboardingStepCompleted;
use Modules\ProviderOnboarding\Models\IntegrationSetting;

class NotifyAdminOnManualReview
{
    public function handle(OnboardingStepCompleted $event): void
    {
        if ($event->status !== 'manual_review') {
            return;
        }

        $provider = $event->user->load('providerVerification')->providerVerification;
        $adminEmails = User::role(RoleEnum::ADMIN)->pluck('email')->toArray();

        $subject = "Требуется ручная проверка паспорта — {$event->user->name}";
        $body = "Пользователь {$event->user->name} (ID: {$event->user->id}) загрузил документы для верификации паспорта. Перейдите в панель администратора для проверки.";

        foreach ($adminEmails as $email) {
            try {
                Mail::raw($body, function ($m) use ($email, $subject) {
                    $m->to($email)->subject($subject);
                });
            } catch (\Throwable $e) {
                Log::error('NotifyAdminOnManualReview: mail failed', ['email' => $email, 'error' => $e->getMessage()]);
            }
        }

        $this->notifyTelegram($subject, $body);
    }

    private function notifyTelegram(string $subject, string $body): void
    {
        $botToken = IntegrationSetting::get('telegram_bot_token');
        $chatId   = IntegrationSetting::get('telegram_admin_chat_id');

        if (!$botToken || !$chatId) {
            return;
        }

        try {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text'    => "📋 {$subject}\n\n{$body}",
            ]);
        } catch (\Throwable $e) {
            Log::error('NotifyAdminOnManualReview: telegram failed', ['error' => $e->getMessage()]);
        }
    }
}
