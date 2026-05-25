<?php
namespace Modules\CloudPayments\Payment;
use Exception;
use App\Helpers\Helpers;
use App\Enums\PaymentStatus;
use App\Http\Traits\PaymentTrait;
use App\Models\PaymentTransactions;
use App\Http\Traits\TransactionsTrait;
class CloudPayments
{
    use PaymentTrait, TransactionsTrait;
    private static function apiRequest(string $endpoint, array $data): array
    {
        $ch = curl_init('https://api.cloudpayments.ru' . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_USERPWD, env('CLOUDPAYMENTS_PUBLIC_ID') . ':' . env('CLOUDPAYMENTS_API_SECRET'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
        if ($err) throw new Exception('CloudPayments cURL error: ' . $err, 500);
        $result = json_decode($response, true);
        if (!$result) throw new Exception('CloudPayments: invalid response', 500);
        return $result;
    }
    public static function getIntent($obj, $request)
    {
        try {
            $paymentAmount = $request->amount ?? $obj?->total;
            $paymentTransaction = PaymentTransactions::updateOrCreate(
                ['item_id' => $obj?->id, 'type' => $request->type],
                ['item_id' => $obj?->id, 'transaction_id' => uniqid('cp_'), 'amount' => $paymentAmount,
                 'payment_method' => config('cloudpayments.name'), 'payment_status' => PaymentStatus::PENDING,
                 'type' => $request->type, 'request_type' => $request->request_type]
            );
            $result = self::apiRequest('/orders/create', [
                'Amount' => Helpers::roundNumber($paymentAmount),
                'Currency' => Helpers::getDefaultCurrencyCode() ?: 'RUB',
                'Description' => config('app.name') . ' — заказ #' . $obj?->id,
                'InvoiceId' => (string) $obj?->id,
                'AccountId' => (string) ($obj?->consumer_id ?? ''),
                'SuccessRedirectUrl' => route('cloudpayments.webhook', ['item_id' => $obj?->id, 'type' => $request->type, 'status' => 'success']),
                'FailRedirectUrl'    => route('cloudpayments.webhook', ['item_id' => $obj?->id, 'type' => $request->type, 'status' => 'fail']),
            ]);
            if (!($result['Success'] ?? false)) throw new Exception('CloudPayments order error: ' . ($result['Message'] ?? 'unknown'), 500);
            $orderId = $result['Model']['Id'] ?? $paymentTransaction->transaction_id;
            self::updatePaymentTransactionId($paymentTransaction, $orderId);
            return ['item_id' => $obj?->id, 'url' => $result['Model']['Url'] ?? '', 'transaction_id' => $orderId, 'is_redirect' => true, 'type' => $request->type];
        } catch (Exception $e) {
            self::updatePaymentStatusByType($obj?->id, $request?->type, PaymentStatus::FAILED);
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
    public static function webhook($request)
    {
        try {
            $paymentTransaction = PaymentTransactions::where(['item_id' => $request->item_id ?? $request->InvoiceId, 'type' => $request->type ?? 'booking'])->first();
            if (!$paymentTransaction) throw new Exception('CloudPayments: transaction not found', 404);
            $hmac = base64_encode(hash_hmac('sha256', $request->getContent(), env('CLOUDPAYMENTS_API_SECRET'), true));
            $received = $request->header('Content-HMAC') ?? $request->header('X-Content-HMAC') ?? '';
            if ($received && !hash_equals($hmac, $received)) throw new Exception('CloudPayments: HMAC mismatch', 403);
            $cpStatus = $request->Status ?? $request->get('status', '');
            $status = match ($cpStatus) { 'Completed', 'success' => PaymentStatus::COMPLETED, 'Declined', 'fail' => PaymentStatus::FAILED, default => PaymentStatus::PENDING };
            return self::updatePaymentStatus($paymentTransaction, $status);
        } catch (Exception $e) { throw new Exception($e->getMessage(), $e->getCode()); }
    }
}
