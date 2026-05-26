<?php

namespace Modules\ProviderOnboarding\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\ProviderOnboarding\Models\OnboardingLog;
use Modules\ProviderOnboarding\Models\ProviderVerification;

class PurgePassportFiles extends Command
{
    protected $signature   = 'onboarding:purge-passport-files {--days=90 : Хранить файлы N дней после одобрения}';
    protected $description = 'Удаляет файлы паспортов по истечении срока хранения (ФЗ-152)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $verifications = ProviderVerification::where('passport_status', 'approved')
            ->where('passport_verified_at', '<=', $cutoff)
            ->whereNotNull('passport_photo_path')
            ->orWhere(function ($q) use ($cutoff) {
                $q->whereNotNull('passport_selfie_path')
                  ->where('passport_status', 'approved')
                  ->where('passport_verified_at', '<=', $cutoff);
            })
            ->get();

        $deleted = 0;

        foreach ($verifications as $v) {
            foreach (['passport_photo_path', 'passport_selfie_path'] as $field) {
                if ($v->$field && Storage::disk('local')->exists($v->$field)) {
                    Storage::disk('local')->delete($v->$field);
                    $deleted++;
                }
            }

            $v->update([
                'passport_photo_path'  => null,
                'passport_selfie_path' => null,
            ]);

            OnboardingLog::record($v->user_id, 'passport', 'files_purged', [
                'reason'           => 'fz152_retention_expired',
                'days'             => $days,
                'verified_at'      => $v->passport_verified_at?->toDateString(),
            ]);
        }

        $this->info("Удалено файлов: {$deleted} (записей: {$verifications->count()})");

        return self::SUCCESS;
    }
}
