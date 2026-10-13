<?php

namespace App\Support\Npd;

use App\Models\NpdReceipt;
use RuntimeException;

/**
 * Selected in admin but not built yet. Receipts stay "failed" with this
 * message and are retried once the channel exists — nothing is lost.
 */
class UnavailableChannel implements NpdReceiptChannel
{
    public function __construct(private string $why) {}

    public function register(NpdReceipt $receipt): array
    {
        throw new RuntimeException($this->why);
    }

    public function cancel(NpdReceipt $receipt, string $reason): void
    {
        throw new RuntimeException($this->why);
    }
}
