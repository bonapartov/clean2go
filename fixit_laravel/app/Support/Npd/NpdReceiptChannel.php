<?php

namespace App\Support\Npd;

use App\Models\NpdReceipt;

/**
 * Where receipts of self-employed executors are actually registered.
 * Implementations: MockChannel (test, nothing leaves the server),
 * UnavailableChannel (placeholder until a real one exists). Real ones come
 * later — ФНС partner API (needs the ООО's partner agreement) or a payout
 * provider (bank / ЮKassa, ROADMAP Sprint 9).
 */
interface NpdReceiptChannel
{
    /** @return array{external_id:string, receipt_url:?string} */
    public function register(NpdReceipt $receipt): array;

    public function cancel(NpdReceipt $receipt, string $reason): void;
}
