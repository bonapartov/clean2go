<?php

namespace Modules\Stripe\Payment;

use Exception;
use App\Helpers\Helpers;
use Stripe\StripeClient;
use App\Enums\PaymentStatus;
use App\Http\Traits\PaymentTrait;
use App\Models\PaymentTransactions;
use Modules\Stripe\Enums\StripeEvent;
use App\Http\Traits\TransactionsTrait;

class Stripe
{
    use PaymentTrait, TransactionsTrait;

    public static function getProvider()
    {
        return new StripeClient(env('STRIPE_SECRET_KEY'));
    }


    private static function getOrCreateStripeCustomer($provider, $user)
    {
        return $provider->customers->create([
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }

    public static function getPlanInterval($plan = null, $frequency = null)
    {
        // Support both plan object and direct frequency parameter
        $duration = $frequency ?? $plan->duration ?? null;
        
        if (!$duration) {
            return 'month'; // Default to monthly
        }
        
        switch($duration) {
            case 'monthly':
                return 'month';
            case 'yearly':
                return 'year';
            case 'weekly':
                return 'week';
            case 'daily':
                return 'day';
        }

        return $duration;
    }

    private static function getOrCreatePrice($provider, $obj, $request, $paymentAmount = null)
    {
        // Use provided payment amount or fall back to booking total
        $amount = $paymentAmount ?? $obj->total;
        
        // Get frequency from request, recurring booking, or plan (for provider plans only)
        // For recurring bookings, frequency comes from request or recurring booking object
        // For provider plans, it comes from plan object
        $frequency = $request->frequency 
            ?? (isset($obj->frequency) ? $obj->frequency : null)
            ?? (isset($obj->plan) && isset($obj->plan->duration) ? $obj->plan->duration : null)
            ?? 'monthly';
        
        // Get service name for product name if available
        $productName = config('app.name') . ' Subscription';
        if (isset($obj->service) && isset($obj->service->title)) {
            $productName = $obj->service->title . ' - Subscription';
        } elseif (isset($obj->service_id)) {
            $service = \App\Models\Service::find($obj->service_id);
            if ($service) {
                $productName = $service->title . ' - Subscription';
            }
        }
        
        $product = $provider->products->create([
            'name' => $productName,
        ]);

        $price = $provider->prices->create([
            'product' => $product->id,
            'unit_amount' => Helpers::currencyConvert($request->code ?? Helpers::getDefaultCurrencyCode(),Helpers::roundNumber($amount)) * 100,
            'currency' => Helpers::getDefaultCurrencyCode(),
            'recurring' => [
                'interval' => self::getPlanInterval($obj->plan ?? null, $frequency),
            ],
        ]);

       return $price;
    }

    public static function createSubscription($provider, $obj, $request, $paymentAmount = null)
    {
        $price = self::getOrCreatePrice($provider , $obj, $request, $paymentAmount);
        $transaction = $provider->checkout->sessions->create([
            'mode' => 'subscription',
            'success_url' => route('stripe.webhook', ['item_id' => $obj?->id, 'type' =>  $request->type]),
            'cancel_url' => route('stripe.webhook', ['item_id' => $obj?->id, 'type' =>  $request->type]),
            'metadata' => [
                'order_number' => $obj?->id,
            ],
            'line_items' => [
                [
                    'price' => $price->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        return $transaction;
    }

    public static function getIntent($obj, $request)
    {
        try {
            // Use request amount if available (for advance payment), otherwise use booking total
            $paymentAmount = $request->amount ?? $obj?->total;
            
            $paymentTransaction = PaymentTransactions::updateOrCreate([
                'item_id' => $obj?->id,
                'type' => $request->type,
            ], [
                'item_id' => $obj?->id,
                'transaction_id' => uniqid(),
                'amount' => $paymentAmount,
                'payment_method' => config('stripe.name'),
                'payment_status' => PaymentStatus::PENDING,
                'type' => $request->type,
                'request_type' => $request->request_type
            ]);

            $provider = self::getProvider();
            if ($request->type == 'subscription' || $request->type == 'recurring_booking') {
                $transaction = self::createSubscription($provider, $obj, $request, $paymentAmount);
            } else {
                $transaction = $provider->checkout->sessions->create([
                    'mode' => 'payment',
                    'success_url' => route('stripe.webhook', ['item_id' => $obj?->id, 'type' => $request->type]),
                    'cancel_url' => route('stripe.webhook', ['item_id' => $obj?->id, 'type' => $request->type]),
                    'metadata' => [
                        'order_number' => $obj?->id,
                    ],
                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' => Helpers::getDefaultCurrencyCode(),
                                'product_data' => [
                                    'name' => config('app.name'),
                                ],
                                'unit_amount' => Helpers::roundNumber($paymentAmount) * 100,
                            ],
                            'quantity' => 1,
                        ],
                    ],
                ]);
            }

            if ($transaction) {
                self::updatePaymentTransactionId($paymentTransaction, $transaction?->id);
                return [
                    'item_id' => $obj?->id,
                    'url' => $transaction->url,
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

    public static function webhook($request)
    {
        try {

            $paymentTransaction = PaymentTransactions::where([
                'item_id' => $request->item_id, 'type' => $request->type,
            ])->first();

            $provider = self::getProvider();
            $payment = $provider->checkout->sessions?->retrieve($paymentTransaction->transaction_id);
            switch ($payment->payment_status) {
                case StripeEvent::PAID:
                    $status = PaymentStatus::COMPLETED;
                    break;

                case StripeEvent::FAILED:
                    $status = PaymentStatus::FAILED;
                    break;

                default:
                    $status = PaymentStatus::PENDING;
            }

            return self::updatePaymentStatus($paymentTransaction, $status);

        } catch (Exception $e) {

            self::updatePaymentStatusByType($request->item_id, $request->type, PaymentStatus::FAILED);
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
