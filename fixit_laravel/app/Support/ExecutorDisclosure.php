<?php

namespace App\Support;

use App\Models\User;
use Modules\ProviderOnboarding\Models\ProviderVerification;

/**
 * Who the client is actually contracting with, shown before payment.
 *
 * Clean2Go is an agent acting on behalf of the executor (ROADMAP, «План от
 * 13.10.2026 — агентская модель B2C», Sprint 6), so the client must see the
 * executor's name, tax status and ИНН before paying (ЗоЗПП ст. 12 —
 * достоверная информация об исполнителе).
 *
 * Data comes from the onboarding verification (provider_verifications), not
 * from fields the provider can type freely. A provider without a passed ИНН
 * check is returned with verified = false and no ИНН, so the page can say so
 * instead of showing nothing.
 *
 * Own code (not Fixit) — see docs/code-inventory.md.
 */
class ExecutorDisclosure
{
    /** taxpayer_type → what the client reads. */
    public const STATUS_LABELS = [
        'self_employed' => 'Самозанятый (НПД)',
        'ip_on_npd' => 'ИП на НПД',
        'individual_entrepreneur' => 'Индивидуальный предприниматель',
        'legal_entity' => 'Юридическое лицо',
    ];

    /**
     * @return array{provider_id:int, name:string, status:?string, status_label:?string, inn:?string, ogrn:?string, verified:bool}
     */
    public static function forProvider(User $provider): array
    {
        $verification = ProviderVerification::where('user_id', $provider->id)->first();
        $verified = $verification && $verification->inn && $verification->inn_status === 'active';

        // A self-employed person has no legal_name: the contract is with
        // them personally, so their own name is the right one to show.
        $name = ($verified ? $verification->legal_name : null) ?: $provider->name;
        $status = $verified ? $verification->taxpayer_type : null;

        return [
            'provider_id' => (int) $provider->id,
            'name' => (string) $name,
            'status' => $status,
            'status_label' => $status ? (self::STATUS_LABELS[$status] ?? null) : null,
            'inn' => $verified ? $verification->inn : null,
            'ogrn' => $verified ? $verification->ogrn : null,
            'verified' => (bool) $verified,
        ];
    }

    /**
     * One entry per distinct provider, in the order given.
     *
     * @param  iterable<int|null>  $providerIds
     * @return list<array>
     */
    public static function forProviderIds(iterable $providerIds): array
    {
        $ids = [];
        foreach ($providerIds as $id) {
            if ($id && ! in_array((int) $id, $ids, true)) {
                $ids[] = (int) $id;
            }
        }

        if (! $ids) {
            return [];
        }

        $providers = User::whereIn('id', $ids)->get()->keyBy('id');

        $result = [];
        foreach ($ids as $id) {
            if ($providers->has($id)) {
                $result[] = self::forProvider($providers[$id]);
            }
        }

        return $result;
    }

    /**
     * Provider ids out of a CheckoutTrait::calculateCosts() result.
     *
     * @return list<int>
     */
    public static function providerIdsFromCheckout(array $items): array
    {
        $ids = [];
        foreach ($items['services'] ?? [] as $service) {
            $ids[] = $service['provider_id'] ?? null;
        }
        foreach ($items['services_package'] ?? [] as $package) {
            foreach ($package['services'] ?? [] as $service) {
                $ids[] = $service['provider_id'] ?? null;
            }
        }

        return array_values(array_filter($ids));
    }
}
