<?php
namespace Modules\YooKassa\Payment;
use Exception;
use App\Helpers\Helpers;
use App\Enums\PaymentStatus;
use App\Http\Traits\PaymentTrait;
use App\Models\PaymentTransactions;
use App\Http\Traits\TransactionsTrait;
class YooKassa
{
    use PaymentTrait, TransactionsTrait;
    private static function apiRequest(string $endpoint, array $data, string $idempotenceKey = ''): array
    {
        $ch = curl_init('https://api.yookassa.ru/v3' . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_USERPWD, env('YOOKASSA_SHOP_ID') . ':' . env('YOOKASSA_SECRET_KEY'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Idempotence-Key: ' . ($idempotenceKey ?: uniqid('yk_', true))]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch); $err = curl_error($ch); $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($err) throw new Exception('YooKassa cURL error: ' . $err, 500);
        $result = json_decode($response, true);
        if (!$result) throw new Exception('YooKassa: invalid response', 500);
        if ($httpStatus >= 400) throw new Exception('YooKassa API error: ' . ($result['description'] ?? $result['code'] ?? 'unknown'), $httpStatus);
        return $result;
    }
    public static function getIntent($obj, $request)
    {
        try {
            $paymentAmount = $request->amount ?? $obj?->total;
            $idempotenceKey = 'yk_' . $obj?->id . '_' . $request->type . '_' . time();
            $paymentTransaction = PaymentTransactions::updateOrCreate(
                ['item_id' => $obj?->id, 'type' => $request->type],
                ['item_id' => $obj?->id, 'transaction_id' => $idempotenceKey, 'amount' => $paymentAmount,
                 'payment_method' => config('yookassa.name'), 'payment_status' => PaymentStatus::PENDING,
                 'type' => $request->type, 'request_type' => $request->request_type]
            );
            $result = self::apiRequest('/payments', [
                'amount' => ['value' => number_format(Helpers::roundNumber($paymentAmount), 2, '.', ''), 'currency' => Helpers::getDefaultCurrencyCode() ?: 'RUB'],
                'confirmation' => ['type' => 'redirect', 'return_url' => route('yookassa.webhook', ['item_id' => $obj?->id, 'type' => $request->type])],
                'capture' => true, 'description' => config('app.name') . ' — заказ #' . $obj?->id,
                'metadata' => ['item_id' => (string) $obj?->id, 'type' => $request->type],
            ], $idempotenceKey);
            if (($result['status'] ?? '') === 'canceled') throw new Exception('YooKassa payment canceled', 400);
            $ykId = $result['id'] ?? $idempotenceKey;
            self::updatePaymentTransactionId($paymentTransaction, $ykId);
            return ['item_id' => $obj?->id, 'url' => $result['confirmation']['confirmation_url'] ?? '', 'transaction_id' => $ykId, 'is_redirect' => true, 'type' => $request->type];
        } catch (Exception $e) { self::updatePaymentStatusByType($obj?->id, $request?->type, PaymentStatus::FAILED); throw new Exception($e->getMessage(), $e->getCode()); }
    }
    public static function webhook($request)
    {
        try {
            $paymentTransaction = PaymentTransactions::where(['item_id' => $request->item_id, 'type' => $request->type ?? 'booking'])->first();
            if (!$paymentTransaction) throw new Exception('YooKassa: transaction not found', 404);
            $ch = curl_init('https://api.yookassa.ru/v3/payments/' . $paymentTransaction->transaction_id);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, env('YOOKASSA_SHOP_ID') . ':' . env('YOOKASSA_SECRET_KEY'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $payment = json_decode(curl_exec($ch), true); curl_close($ch);
            $status = match ($payment['status'] ?? '') { 'succeeded' => PaymentStatus::COMPLETED, 'canceled' => PaymentStatus::FAILED, default => PaymentStatus::PENDING };
            return self::updatePaymentStatus($paymentTransaction, $status);
        } catch (Exception $e) { throw new Exception($e->getMessage(), $e->getCode()); }
    }
}
