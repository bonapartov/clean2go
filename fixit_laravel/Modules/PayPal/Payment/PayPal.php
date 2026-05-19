<?php

namespace Modules\PayPal\Payment;

use App\Enums\PaymentMethod;
use Exception;
use App\Helpers\Helpers;
use Illuminate\Support\Str;
use App\Enums\PaymentStatus;
use App\Exceptions\ExceptionHandler;
use App\Http\Traits\PaymentTrait;
use App\Models\PaymentTransactions;
use Modules\PayPal\Enums\PaypalEvent;
use Modules\PayPal\Enums\PaypalCurrencies;

class PayPal
{
    use PaymentTrait;

    public static function getPayPalConfigs()
    {
        return config('paypal.configs');
    }

    public static function getPayPalPaymentUrl()
    {
        $paypal = self::getPayPalConfigs();

        return ($paypal['paypal_mode'] == '1') ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    public static function getAccessToken()
    {
        $paypal = self::getPayPalConfigs();
        $payment_url = self::getPayPalPaymentUrl();
        if (! empty($paypal)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $payment_url.'/v1/oauth2/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
            curl_setopt($ch, CURLOPT_USERPWD, @$paypal['paypal_client_id'].':'.@$paypal['paypal_client_secret']);

            $headers = [];
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $accessToken = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:'.curl_error($ch);
            }
            curl_close($ch);
            return json_decode($accessToken, true);
        }
    }

    public static function getPlanInterval($frequency = null)
    {
        if (!$frequency) {
            return 'MONTH'; // Default to monthly
        }
        
        switch(strtolower($frequency)) {
            case 'weekly':
                return 'WEEK';
            case 'monthly':
                return 'MONTH';
            case 'yearly':
                return 'YEAR';
            default:
                return 'MONTH';
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
                'payment_method' => config('paypal.name'),
                'payment_status' => PaymentStatus::PENDING,
                'type' => $request->type,
                'request_type' => $request->request_type ?? null
            ]);

            $defaultCurrencyCode = Helpers::getDefaultCurrencyCode();
            if (! in_array($defaultCurrencyCode, array_column(PaypalCurrencies::cases(), 'value'))) {
                throw new Exception($defaultCurrencyCode.' currency code is not support for PayPal.', 400);
            }

            $token = self::getAccessToken();
            $payment_url = self::getPayPalPaymentUrl();
            
            if (isset($token['access_token'])) {
                // Get service name for plan name if available
                $planName = config('app.name') . ' Subscription Plan';
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
                
                // Create a billing plan for subscription
                $planPayload = [
                    'product_id' => 'PROD-' . uniqid(),
                    'name' => $planName,
                    'description' => $planDescription,
                    'status' => 'ACTIVE',
                    'billing_cycles' => [
                        [
                            'frequency' => [
                                'interval_unit' => self::getPlanInterval($frequency),
                                'interval_count' => 1,
                            ],
                            'tenure_type' => 'REGULAR',
                            'sequence' => 1,
                            'total_cycles' => 0, // 0 means infinite
                            'pricing_scheme' => [
                                'fixed_price' => [
                                    'value' => Helpers::currencyConvert($request->currency_code ?? $defaultCurrencyCode, Helpers::roundNumber($paymentAmount)),
                                    'currency_code' => $defaultCurrencyCode,
                                ],
                            ],
                        ],
                    ],
                    'payment_preferences' => [
                        'auto_bill_outstanding' => true,
                        'setup_fee' => [
                            'value' => '0',
                            'currency_code' => $defaultCurrencyCode,
                        ],
                        'setup_fee_failure_action' => 'CONTINUE',
                        'payment_failure_threshold' => 3,
                    ],
                ];

                // Create billing plan
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $payment_url.'/v1/billing/plans');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($planPayload));

                $headers = [];
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Authorization: Bearer '.$token['access_token'];
                $headers[] = 'Paypal-Request-Id:'.Str::uuid();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                
                $planResponse = curl_exec($ch);
                if (curl_errno($ch)) {
                    throw new Exception('Error: '.curl_error($ch), 500);
                }
                curl_close($ch);
                $planData = json_decode($planResponse, true);

                if (!isset($planData['id'])) {
                    throw new Exception('Failed to create PayPal billing plan', 500);
                }

                $planId = $planData['id'];

                // Create subscription
                $subscriptionPayload = [
                    'plan_id' => $planId,
                    'start_time' => date('c', strtotime('+1 minute')),
                    'subscriber' => [
                        'name' => [
                            'given_name' => $obj->consumer->name ?? 'Customer',
                        ],
                        'email_address' => $obj->consumer->email ?? '',
                    ],
                    'application_context' => [
                        'brand_name' => config('app.name'),
                        'locale' => 'en-US',
                        'shipping_preference' => 'NO_SHIPPING',
                        'user_action' => 'SUBSCRIBE_NOW',
                        'payment_method' => [
                            'payer_selected' => 'PAYPAL',
                            'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                        ],
                        'return_url' => route('paypal.status'),
                        'cancel_url' => route('paypal.status'),
                    ],
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $payment_url.'/v1/billing/subscriptions');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($subscriptionPayload));

                $headers = [];
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Authorization: Bearer '.$token['access_token'];
                $headers[] = 'Paypal-Request-Id:'.Str::uuid();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                
                $subscriptionResponse = curl_exec($ch);
                if (curl_errno($ch)) {
                    throw new Exception('Error: '.curl_error($ch), 500);
                }
                curl_close($ch);
                $subscriptionData = json_decode($subscriptionResponse, true);

                if (isset($subscriptionData['links']) && isset($subscriptionData['id'])) {
                    self::updatePaymentTransactionId($paymentTransaction, $subscriptionData['id']);

                    // Find approval URL
                    $approvalUrl = null;
                    foreach ($subscriptionData['links'] as $link) {
                        if ($link['rel'] === 'approve') {
                            $approvalUrl = $link['href'];
                            break;
                        }
                    }

                    return [
                        'item_id' => $obj?->id,
                        'url' => $approvalUrl,
                        'transaction_id' => $subscriptionData['id'],
                        'type' => $request->type,
                        'is_redirect' => true,
                        'subscription_id' => $subscriptionData['id'],
                    ];
                }
            }

            throw new Exception('Something went wrong in PayPal gateway', 500);
        } catch (Exception $e) {
            self::updatePaymentStatusByType($obj?->id, $request?->type, PaymentStatus::FAILED);
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public static function getIntent($obj, $request)
    {
        try {
            // Check if this is a recurring booking/subscription
            if ($request->type == 'recurring_booking' || $request->type == 'subscription') {
                return self::createSubscription($obj, $request);
            }

            // Determine payment amount with priority:
            // 1. If advance payment is enabled, ALWAYS use advance_payment_amount (most important)
            // 2. Otherwise, use request->amount if set and valid
            // 3. Fallback to booking total
            $paymentAmount = null;
            
            // First priority: Check if advance payment is enabled (check multiple ways)
            $isAdvanceEnabled = false;
            if (isset($obj->is_advance_payment_enabled)) {
                $isAdvanceEnabled = (bool) $obj->is_advance_payment_enabled;
            } elseif (property_exists($obj, 'is_advance_payment_enabled')) {
                $isAdvanceEnabled = (bool) $obj->is_advance_payment_enabled;
            }
            
            $advanceAmount = isset($obj->advance_payment_amount) ? (float) $obj->advance_payment_amount : 0;
            
            // If advance payment is enabled and we have an advance amount, ALWAYS use it
            if ($isAdvanceEnabled && $advanceAmount > 0) {
                $paymentAmount = $advanceAmount;
            } else {
                // Second priority: Try to get amount from request (multiple ways)
                $requestAmount = $request->input('amount') ?? $request->get('amount') ?? ($request->has('amount') ? $request->amount : null);
                
                if ($requestAmount && $requestAmount > 0) {
                    $paymentAmount = (float) $requestAmount;
                } else {
                    // Fallback: Use booking total
                    $paymentAmount = isset($obj->total) ? (float) $obj->total : 0;
                }
            }
            
            // Final validation - ensure we have a valid amount
            if (!$paymentAmount || $paymentAmount <= 0) {
                $paymentAmount = isset($obj->total) ? (float) $obj->total : 0;
            }

            $payment = PaymentTransactions::updateOrCreate([
                'item_id' => $obj?->id,
                'type' => $request->type,
            ], [
                'item_id' => $obj?->id,
                'transaction_id' => uniqid(),
                'amount' => $paymentAmount,
                'payment_method' => config('paypal.name'),
                'payment_status' => PaymentStatus::COMPLETED,
                'type' => $request->type,
                'request_type' => $request->request_type
            ]);

            $defaultCurrencyCode = Helpers::getDefaultCurrencyCode();
            if (! in_array($defaultCurrencyCode, array_column(PaypalCurrencies::cases(), 'value'))) {
                throw new Exception($defaultCurrencyCode.' currency code is not support for PayPal.', 400);
            }

            $token = self::getAccessToken();
            $payment_url = self::getPayPalPaymentUrl();
            if (isset($token['access_token'])) {
                $payload = [];
                $payload['intent'] = 'CAPTURE';
                $payload['purchase_units'] = [
                    [
                        'invoice_id' => $obj?->id,
                        'amount' => [
                            'currency_code' => Helpers::getDefaultCurrencyCode(),
                            'value' => Helpers::currencyConvert($request->currency_code ?? Helpers::getDefaultCurrencyCode(),Helpers::roundNumber($paymentAmount)),
                        ],
                        'description' => 'Details From '.config('app.name'),
                    ],
                ];
                $payload['application_context'] = [
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW',
                    'payment_method' => [
                        'payer_selected' => 'PAYPAL',
                        'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                    ],
                    'return_url' => route('paypal.status'),
                    'cancel_url' => route('paypal.status'),
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $payment_url.'/v2/checkout/orders');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

                $headers = [];
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Authorization: Bearer '.$token['access_token'];
                $headers[] = 'Paypal-Request-Id:'.Str::uuid();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $transaction = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error:'.curl_error($ch);
                }
                curl_close($ch);
                $transaction = json_decode($transaction, true);

                if (isset($transaction['links']) && isset($transaction['id'])) {
                    self::updatePaymentTransactionId($payment, $transaction['id']);

                    return [
                        'item_id' => $obj?->id,
                        'url' => next($transaction['links'])['href'],
                        'transaction_id' => $transaction['id'],
                        'type' => $request->type,
                        'is_redirect' => true,
                    ];
                }
            }

            throw new Exception('Something went to wrong in paypal gateway', 500);
        } catch (Exception $e) {


            self::updatePaymentStatusByType($obj?->id, $request?->type, PaymentStatus::FAILED);

            throw new ExceptionHandler($e->getMessage(), $e->getCode());

        }
    }

    public static function status($transaction_id)
    {

        try {
            if(config('app.demo')){
                $token = self::getAccessToken();
                $payment_url = self::getPayPalPaymentUrl();
                if (isset($token['access_token'])) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $payment_url."/v2/checkout/orders/{$transaction_id}/capture");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);
    
                    $headers = [];
                    $headers[] = 'Content-Type: application/json';
                    $headers[] = 'Authorization: Bearer '.$token['access_token'];
                    $headers[] = 'Paypal-Request-Id:'.Str::uuid();
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $payment = curl_exec($ch);
                    if (curl_errno($ch)) {
                        echo 'Error:'.curl_error($ch);
                    }
                    curl_close($ch);
                    $payment = json_decode($payment);
                    $payment_status = PaymentStatus::PENDING;
                    $paymentTransaction = self::getPaymentByTransactionId($transaction_id);
                    if (isset($payment?->status)) {
                        $payment_status = $payment?->status;
                    } elseif (!isset($payment?->status) && isset($payment?->details)) {
                        if (head($payment?->details)?->issue == PaypalEvent::ORDER_ALREADY_CAPTURED) {
                            return $paymentTransaction;
                        }
                    } else {
                        $payment_status = PaymentStatus::FAILED;
                    }
    
                    return self::updatePaymentStatus($paymentTransaction, $payment_status);
                }
    
                throw new Exception('Provided transaction id is invalid!', 400);
            } else {
                throw new Exception('This action is disable in demo mode!', 400);
            }
            } catch (Exception $e) {


            self::updatePaymentStatusByTrans($transaction_id, PaymentStatus::FAILED);
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function getPaymentByTransactionId($transaction_id)
    {
        return PaymentTransactions::where('transaction_id', $transaction_id)->first();
    }

    public static function getPaymentByItemId($item_id)
    {
        return PaymentTransactions::where('item_id', $item_id)->first();
    }

    public static function webhook($request)
    {
        try {
            $config = self::getPayPalConfigs();
            $payment_url = self::getPayPalPaymentUrl();
            $token = self::getAccessToken();

            if (isset($token['access_token'])) {
                $payload = [
                    'auth_algo' => $request->header('PAYPAL-AUTH-ALGO', null),
                    'cert_url' => $request->header('PAYPAL-CERT-URL', null),
                    'transmission_id' => $request->header('PAYPAL-TRANSMISSION-ID', null),
                    'transmission_sig' => $request->header('PAYPAL-TRANSMISSION-SIG', null),
                    'transmission_time' => $request->header('PAYPAL-TRANSMISSION-TIME', null),
                    'webhook_id' => $config->webhook_id,
                    'webhook_event' => $request->all(),
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $payment_url.'/v1/notifications/verify-webhook-signature');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

                $headers = [];
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Authorization: Bearer '.$token['access_token'];
                $headers[] = 'Paypal-Request-Id:'.Str::uuid();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $event = curl_exec($ch);
                if (! isset($event['verification_status'])) {
                    throw new Exception($event['error']['name'], 500);
                }

                switch ($request->event_type) {
                    case PaypalEvent::COMPLETED:
                        $payment_status = PaymentStatus::COMPLETED;
                        break;

                    case PaypalEvent::PENDING:
                        $payment_status = PaymentStatus::PENDING;
                        break;

                    case PaypalEvent::REFUNDED:
                        $payment_status = PaymentStatus::REFUNDED;
                        break;

                    case PaypalEvent::DECLINED:
                    case PaypalEvent::CANCELLED:
                        $payment_status = PaymentStatus::CANCELLED;
                        break;

                    default:
                        $payment_status = PaymentStatus::FAILED;
                }

                $paymentTransaction = self::getPaymentByItemId($request->resource['invoice_id']);

                return self::updatePaymentStatus($paymentTransaction, $payment_status);
            }

            throw new Exception('Provided transactions id is invalid!', 400);
        } catch (Exception $e) {

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
