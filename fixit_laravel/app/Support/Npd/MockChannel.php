<?php

namespace App\Support\Npd;

use App\Models\NpdReceipt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/** Test channel: pretends ФНС accepted the receipt. Nothing is sent anywhere. */
class MockChannel implements NpdReceiptChannel
{
    public function register(NpdReceipt $receipt): array
    {
        Log::info('NPD receipt (mock) registered', ['receipt' => $receipt->id, 'inn' => $receipt->executor_inn, 'amount' => $receipt->amount]);

        return ['external_id' => 'TEST-'.Str::upper(Str::random(10)), 'receipt_url' => null];
    }

    public function cancel(NpdReceipt $receipt, string $reason): void
    {
        Log::info('NPD receipt (mock) cancelled', ['receipt' => $receipt->id, 'reason' => $reason]);
    }
}
