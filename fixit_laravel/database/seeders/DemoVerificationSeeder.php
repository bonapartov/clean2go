<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\ProviderOnboarding\Models\ProviderVerification;

/**
 * Demo verifications for the demo providers of DemoCatalogSeeder, so the
 * «Исполнитель услуги» block (ROADMAP Sprint 6) shows a status and ИНН on
 * demo orders instead of «данные не проверены».
 *
 * ИНН and ОГРН are checksum-valid but use region code 00, which does not
 * exist — they cannot belong to a real taxpayer. Each record is marked as
 * demo in manual_review_reason. Idempotent: keyed by user_id.
 *
 * Own code (not Fixit).
 */
class DemoVerificationSeeder extends Seeder
{
    private const DEMO_NOTE = 'Демо-данные: ИНН и ОГРН с несуществующим кодом региона 00, такого налогоплательщика нет.';

    public function run(): void
    {
        $seq = 0;

        foreach (['Демо Иванов', 'Демо Петров', 'Демо Сидоров', 'Демо Кузнецова', 'Демо Смирнова'] as $name) {
            $this->verify($name, [
                'taxpayer_type' => 'self_employed',
                'inn' => self::personInn(++$seq),
                'legal_name' => null,
                'ogrn' => null,
                'npd_status' => 'active',
                'contract_type' => 'self_employed',
            ]);
        }

        foreach (['ИП Демо Волков', 'ИП Демо Морозов', 'ИП Демо Соколова', 'ИП Демо Новикова', 'ИП Демо Лебедев'] as $name) {
            $seq++;
            $this->verify($name, [
                'taxpayer_type' => 'individual_entrepreneur',
                'inn' => self::personInn($seq),
                'legal_name' => $name,
                'ogrn' => self::ogrnip($seq),
                'npd_status' => 'not_applicable',
                'contract_type' => 'ip',
            ]);
        }

        $companies = [
            'Демо Директор Кленов' => 'ООО «Демо Клининг Сервис»',
            'Демо Директор Рябов' => 'ООО «Демо РемонтГрупп»',
        ];
        foreach ($companies as $director => $companyName) {
            $seq++;
            $this->verify($director, [
                'taxpayer_type' => 'legal_entity',
                'inn' => self::companyInn($seq),
                'legal_name' => $companyName,
                'director_name' => $director,
                'ogrn' => self::ogrn($seq),
                'npd_status' => 'not_applicable',
                'contract_type' => 'ooo',
            ]);
        }
    }

    private function verify(string $userName, array $attributes): void
    {
        $user = User::where('name', $userName)->where('email', 'like', 'demo.%')->first()
            ?? User::where('name', $userName)->first();
        if (! $user) {
            return;
        }

        ProviderVerification::updateOrCreate(['user_id' => $user->id], array_merge([
            'inn_status' => 'active',
            'inn_verified_at' => now(),
            'onboarding_status' => 'approved',
            'manual_review_reason' => self::DEMO_NOTE,
        ], $attributes));
    }

    /** 12-digit ИНН of a person, region 00. */
    public static function personInn(int $seq): string
    {
        $d = array_map('intval', str_split('0000'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT)));
        $d[] = self::checksum($d, [7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
        $d[] = self::checksum($d, [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);

        return implode('', $d);
    }

    /** 10-digit ИНН of an organisation, region 00. */
    public static function companyInn(int $seq): string
    {
        $d = array_map('intval', str_split('0000'.str_pad((string) $seq, 5, '0', STR_PAD_LEFT)));
        $d[] = self::checksum($d, [2, 4, 10, 3, 5, 9, 4, 6, 8]);

        return implode('', $d);
    }

    /** 13-digit ОГРН (organisation), region 00: check = (first 12 mod 11) mod 10. */
    public static function ogrn(int $seq): string
    {
        $base = '1'.'26'.'00'.'00'.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);

        return $base.(int) bcmod(bcmod($base, '11'), '10');
    }

    /** 15-digit ОГРНИП (sole proprietor), region 00: check = (first 14 mod 13) mod 10. */
    public static function ogrnip(int $seq): string
    {
        $base = '3'.'26'.'00'.'00'.str_pad((string) $seq, 7, '0', STR_PAD_LEFT);

        return $base.(int) bcmod(bcmod($base, '13'), '10');
    }

    private static function checksum(array $digits, array $weights): int
    {
        $sum = 0;
        foreach ($weights as $i => $w) {
            $sum += $digits[$i] * $w;
        }

        return $sum % 11 % 10;
    }
}
