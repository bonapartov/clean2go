<?php

namespace Modules\RazorPay\Payment;

use Exception;
use Razorpay\Api\Api;
use App\Helpers\Helpers;
use App\Enums\PaymentStatus;
use App\Http\Traits\PaymentTrait;
use App\Models\PaymentTransactions;
use App\Http\Traits\TransactionsTrait;
use Modules\RazorPay\Enums\RazorPayEvent;

class RazorPay
{
    use PaymentTrait, TransactionsTrait;

    public static function getProvider()
    {
        return new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
    }

    public static function getPlanInterval($frequency = null)
    {
        if (!$frequency) {
            return 'monthly'; // Default
        }
        
        switch(strtolower($frequency)) {
            case 'weekly':
                return 'weekly';
            case 'monthly':
                return 'monthly';
            case 'yearly':
                return 'yearly';
            default:
                return 'monthly';
        }
    }

    public static function createSubscription($obj, $request)
    {
        try {
            $paymentAmount = $request->amount ?? $obj->total;
            $frequency = $request->frequency ?? $obj->frequency ?? 'monthly';
            
            $paymentTransaction = PaymentTransactions::updateOrCreate([
                'item_id' => $obj?->id,
                'type' => $request->type,
            ], [
                'item_id' => $obj?->id,
                'transaction_id' => uniqid(),
                'amount' => $paymentAmount,
                'payment_method' => config('razorpay.name'),
                'payment_status' => PaymentStatus::PENDING,
                'type' => $request->type,
                'request_type' => $request->request_type ?? null
            ]);

            $provider = self::getProvider();
            
            // Get service name for plan name if available
            $planName = config('app.name') . ' Subscription';
            $planDescription = 'Recurring booking subscription';
            if (isset($obj->service) && isset($obj->service->title)) {
                $planName = $obj->service->title . ' - Subscription';
                $planDescription = 'Recurring ' . $obj->service->title . ' subscription';
            } elseif (isset($obj->service_id)) {
                $service = \App\Models\Service::find($obj->service_id);
                if ($service) {
                    $planName = $service->title . ' - Subscription';
                    $planDescription = 'Recurring ' . $service->title . ' subscription';
                }
            }
            
            // Create a plan first
            $planData = [
                'period' => self::getPlanInterval($frequency),
                'interval' => 1,
                'item' => [
                    'name' => $planName,
                    'description' => $planDescription,
                    'amount' => (Helpers::currencyConvert($request->currency_code ?? Helpers::getDefaultCurrencyCode(), $paymentAmount) * 100),
                    'currency' => 'INR',
                ],
                'notes' => [
                    'item_id' => $obj->id,
                    'type' => $request->type,
                ],
            ];
            
            $plan = $provider->plan->create($planData);
            
            // Create subscription
            $subscriptionData = [
                'plan_id' => $plan->id,
                'total_count' => $request->total_occurrences ?? null, // null means infinite
                'customer_notify' => 1,
                'notes' => [
                    'item_id' => $obj->id,
                    'type' => $request->type,
                ],
            ];
            
            $subscription = $provider->subscription->create($subscriptionData);
            
            if ($subscription) {
                self::updatePaymentTransactionId($paymentTransaction, $subscription->id);

                // Get payment link from subscription
                $paymentLink = null;
                if (isset($subscription->short_url)) {
                    $paymentLink = $subscription->short_url;
                } elseif (isset($subscription->links)) {
                    foreach ($subscription->links as $link) {
                        if ($link->rel === 'authenticate') {
                            $paymentLink = $link->href;
                            break;
                        }
                    }
                }

                return [
                    'item_id' => $obj?->id,
                    'url' => $paymentLink ?? route('razorpay.status', ['item_id' => $obj?->id, 'type' => $request->type]),
                    'transaction_id' => $subscription->id,
                    'subscription_id' => $subscription->id,
                    'is_redirect' => true,
                    'type' => $request->type,
                ];
            }

            throw new Exception('Something went wrong in RazorPay gateway', 500);
        } catch (Exception $e) {
            self::updatePaymentStatusByType($obj?->id, $request?->type, PaymentStatus::FAILED);
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function getIntent($obj, $request)
    {
        try {
            // Check if this is a recurring booking/subscription
            if ($request->type == 'recurring_booking' || $request->type == 'subscription') {
                return self::createSubscription($obj, $request);
            }

            // Use request amount if available (for advance payment), otherwise use booking total
            // Priority: request->amount > obj->advance_payment_amount (if enabled) > obj->total
            $paymentAmount = $request->amount;
            
            // If amount not in request, check if advance payment is enabled
            if (!$paymentAmount && isset($obj->is_advance_payment_enabled) && $obj->is_advance_payment_enabled && $obj->advance_payment_amount > 0) {
                $paymentAmount = $obj->advance_payment_amount;
            }
            
            // Fallback to total if still not set
            if (!$paymentAmount) {
                $paymentAmount = $obj?->total;
            }
            
            $paymentTransaction = PaymentTransactions::updateOrCreate([
                'item_id' => $obj?->id,
                'type' => $request->type,
            ], [
                'item_id' => $obj?->id,
                'transaction_id' => uniqid(),
                'amount' => $paymentAmount,
                'payment_method' => config('razorpay.name'),
                'payment_status' => PaymentStatus::PENDING,
                'type' => $request->type,
                'request_type' => $request->request_type
            ]);

            $provider = self::getProvider();
            $transaction = $provider->paymentLink->create([
                'notes' => [
                    'item_id' => $obj->id,
                    'type' => $request->type,
                ],
                'amount' => (Helpers::currencyConvert($request->currency_code ?? Helpers::getDefaultCurrencyCode(), $paymentAmount) * 100),
                'currency' => 'INR',
                'callback_url' => route('razorpay.status', ['item_id' => $obj?->id, 'type' => $request->type]),
                'description' => 'Order From '.config('app.name'),
            ]);

            if ($transaction) {
                self::updatePaymentTransactionId($paymentTransaction, $transaction?->id);

                return [
                    'item_id' => $obj?->id,
                    'url' => $transaction->short_url,
                    'transaction_id' => $transaction->id,
                    'is_redirect' => true,
                    'type' => $request->type,
                ];
            }

            throw new Exception('Something went to wrong in stripe gateway', 500);
        } catch (Exception $e) {

            self::updatePaymentStatusByType($obj?->id, $request?->type, PaymentStatus::FAILED);



            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function status($request)
    {
        try {

            $provider = self::getProvider();
            $paymentTransaction = PaymentTransactions::where([
                'transaction_id' => $request->razorpay_payment_link_id,
                'type' => $request->type,
            ])
                ->first();

            $transaction_id = $paymentTransaction->transaction_id;
            $payment = $provider->paymentLink->fetch($transaction_id);
            switch ($payment->status) {
                case RazorPayEvent::COMPLETED:
                    $status = PaymentStatus::COMPLETED;
                    break;

                case RazorPayEvent::FAILED:
                    $status = PaymentStatus::FAILED;
                    break;

                default:
                    $status = PaymentStatus::PENDING;
            }

            return self::updatePaymentStatus($paymentTransaction, $status);

        } catch (Exception $e) {

            self::updatePaymentStatusByTrans($request->razorpay_payment_link_id, PaymentStatus::FAILED);
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function webhook($request)
    {
        try {

            $provider = self::getProvider();
            $response = @file_get_contents('php://input');
            $signature = $request->header('X-Razorpay-Signature');
            if ($response && $signature) {
                $provider->utility->verifyWebhookSignature($response, $signature, env('RAZORPAY_WEBHOOK_SECRET_KEY'));
            }

            $item_id = $request->payload['payment_link']['notes']['item_id'];
            $type = $request->payload['payment_link']['notes']['type'];
            $paymentTransaction = PaymentTransactions::where([
                'item_id' => $item_id, 'type' => $type,
            ])->first();
            switch ($request->event) {
                case RazorPayEvent::PAID:
                    $status = PaymentStatus::COMPLETED;
                    break;

                case RazorPayEvent::PARTIALLY_PAID:
                    $status = PaymentStatus::PENDING;
                    break;

                case RazorPayEvent::CANCELLED:
                    $status = PaymentStatus::CANCELLED;
                    break;

                default:
                    $status = PaymentStatus::FAILED;
                    break;
            }

            return self::updatePaymentStatus($paymentTransaction, $status);

        } catch (Exception $e) {

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
