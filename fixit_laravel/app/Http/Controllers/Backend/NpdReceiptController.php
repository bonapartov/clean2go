<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\NpdReceipt;
use App\Support\Npd\NpdReceipts;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin: receipts of self-employed executors («Мой налог»), with a retry
 * button. Menu: «Верификация РФ → Чеки самозанятых». Own code.
 */
class NpdReceiptController extends Controller
{
    public const STATUS_LABELS = [
        'pending' => 'Ожидает отправки',
        'registered' => 'Зарегистрирован',
        'failed' => 'Ошибка отправки',
        'cancel_pending' => 'Аннулируется',
        'cancelled' => 'Аннулирован',
    ];

    public function index(Request $request): View
    {
        $receipts = NpdReceipt::with(['booking:id,booking_number', 'executor:id,name'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('backend.npd-receipts.index', [
            'receipts' => $receipts,
            'statusLabels' => self::STATUS_LABELS,
            'enabled' => NpdReceipts::enabled(),
            'channel' => NpdReceipts::CHANNELS[NpdReceipts::channelKey()],
            'nearLimit' => NpdReceipts::nearLimit(),
            'limits' => NpdReceipts::limits(),
        ]);
    }

    public function retry(): RedirectResponse
    {
        $done = NpdReceipts::retry();

        return back()->with('success', "Отправлено: {$done['sent']}, аннулировано: {$done['cancelled']}, всё ещё с ошибкой: {$done['still_failing']}.");
    }
}
