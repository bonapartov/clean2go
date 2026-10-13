<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\NpdReceipt;
use App\Support\Npd\NpdReceipts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Executor's own receipts and НПД income through the platform this year
 * (provider app «Мои чеки (НПД)», ROADMAP Sprint 8). Own code.
 */
class NpdReceiptController extends Controller
{
    public function mine(Request $request): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $year = (int) now()->year;
        $income = NpdReceipts::yearIncome($userId, $year);
        ['limit' => $limit, 'warn_at' => $warnAt] = NpdReceipts::limits();

        $receipts = NpdReceipt::with('booking:id,booking_number,parent_id')
            ->where('executor_id', $userId)
            ->latest('id')->limit(100)->get()
            ->map(fn (NpdReceipt $r) => [
                'id' => $r->id,
                'booking_number' => $r->booking?->booking_number,
                'service_name' => $r->service_name,
                'amount' => $r->amount,
                'status' => $r->status,
                'status_label' => NpdReceipts::CLIENT_STATUS[$r->status] ?? $r->status,
                'number' => $r->external_id,
                'url' => $r->receipt_url,
                'operation_time' => $r->operation_time?->toIso8601String(),
                'is_test' => $r->channel === 'mock',
            ]);

        return response()->json(['success' => true, 'data' => [
            'enabled' => NpdReceipts::enabled(),
            'year' => $year,
            'income' => $income,
            'limit' => $limit,
            'warn_at' => $warnAt,
            'percent' => $limit > 0 ? round($income / $limit * 100, 1) : 0,
            'receipts' => $receipts,
        ]]);
    }
}
