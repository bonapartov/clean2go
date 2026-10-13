<?php

namespace App\Console\Commands;

use App\Support\Npd\NpdReceipts;
use Illuminate\Console\Command;

/** Retries failed receipt sends and cancellations of self-employed executors. Own code. */
class RetryNpdReceipts extends Command
{
    protected $signature = 'npd:receipts-retry';

    protected $description = 'Повторить отправку и аннулирование чеков самозанятых («Мой налог»)';

    public function handle(): int
    {
        $done = NpdReceipts::retry();
        $this->info("Отправлено: {$done['sent']}, аннулировано: {$done['cancelled']}, всё ещё с ошибкой: {$done['still_failing']}");

        return self::SUCCESS;
    }
}
