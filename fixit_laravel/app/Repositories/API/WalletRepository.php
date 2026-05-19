<?php

namespace App\Repositories\API;

use App\Enums\RoleEnum;
use App\Enums\TransactionType;
use App\Enums\WalletPointsDetail;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Traits\WalletBonusTrait;
use App\Models\ProviderWallet;
use App\Models\User;
use App\Models\Wallet;
use Exception;
use Nwidart\Modules\Facades\Module;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class WalletRepository extends BaseRepository
{
    use WalletBonusTrait;

    protected $user;

    protected $providerWallet;

    protected $fieldSearchable = [
        'transactions.type' => 'like',
        'transactions.detail' => 'like',
    ];

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));
        } catch (ExceptionHandler $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function model()
    {
        $this->user = new User();
        $this->providerWallet = new ProviderWallet();

        return Wallet::class;
    }

    public function credit($request)
    {
        try {
            $wallet = $this->creditWallet($request->consumer_id, $request->balance, WalletPointsDetail::ADMIN, $request->payment_method, $request->payment_id);
            if ($wallet) {
                $wallet->setRelation('transactions', $wallet->transactions()
                    ->paginate($request->paginate ?? $wallet->transactions()->count()));
            }

            return $wallet;
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getWallet($consumer_id)
    {
        $roleName = Helpers::getRoleByUserId($consumer_id);
        if ($roleName == RoleEnum::CONSUMER) {
            return Wallet::firstOrCreate(['consumer_id' => $consumer_id]);
        }

        throw new ExceptionHandler(__('errors.must_be_consumer',['consumer' => RoleEnum::CONSUMER]), 400);
    }

    public function creditWallet($consumer_id, $balance, $detail, $payment_method, $payment_id)
    {
        $wallet = $this->getWallet($consumer_id);
        if ($wallet) {
            $wallet->increment('balance', $balance);
        }

        $this->creditTransaction($wallet, $balance, $detail, $payment_method, $payment_id);

        return $wallet;
    }

    public function storeTransaction($model, $type, $detail, $amount, $payment_method, $payment_id, $order_id = null)
    {
        return $model->transactions()->create([
            'payment_method' => $payment_method,
            'payment_id' => $payment_id,
            'amount' => $amount,
            'order_id' => $order_id,
            'detail' => $detail,
            'type' => $type,
            'from' => $this->getRoleId(),
        ]);
    }

    public function getRoleId()
    {
        $roleName = Helpers::getCurrentRoleName() ?? RoleEnum::ADMIN;
        if ($roleName == RoleEnum::ADMIN) {
            return $this->user->role(RoleEnum::ADMIN)->first()->id;
        }

        return Helpers::getCurrentUserId();
    }

    public function creditTransaction($model, $amount, $detail, $payment_method, $payment_id, $order_id = null)
    {
        return $this->storeTransaction($model, TransactionType::CREDIT, $detail, $amount, $payment_method, $payment_id);
    }

    public function debit($request)
    {
        try {

            $wallet = $this->debitWallet($request->consumer_id, $request->balance, WalletPointsDetail::ADMIN);
            if ($wallet) {
                $wallet->setRelation('transactions', $wallet->transactions()
                    ->paginate($request->paginate ?? $wallet->transactions()->count()));
            }

            return $wallet;
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function topUp($request)
    {
        try {
            $user_id = Helpers::getCurrentUserId();
            $rolename = Helpers::getCurrentRoleName();
            if ($rolename === RoleEnum::CONSUMER) {
                $wallet = $this->getWallet($user_id);

                // Calculate adjusted amount if bonus is admin funded
                // Keep $request->amount unchanged for frontend/mobile compatibility
                $originalAmount = $request->amount;
                $adjustment = $this->calculateAdjustedTopUpAmount($originalAmount);
                
                // Store original amount and bonus info in request for later use in paymentStatus
                $request->merge([
                    'original_topup_amount' => $originalAmount,
                    'wallet_bonus_amount' => $adjustment['bonus_amount'] ?? 0,
                    'wallet_bonus_id' => $adjustment['wallet_bonus']->id ?? null,
                    'is_admin_funded' => $adjustment['is_admin_funded'] ?? false,
                ]);

                return $this->createPayment($wallet, $request, $adjustment);

            } else {
                throw new Exception(__('static.wallet.permission_denied'), 403);
            }
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function createPayment($wallet, $request, $adjustment = null)
    {
        try {

            if ($wallet) {
                $module = Module::find($request->payment_method);
                if (! is_null($module) && $module?->isEnabled()) {
                    $moduleName = $module->getName();
                    $payment = 'Modules\\'.$moduleName.'\\Payment\\'.$moduleName;
                    if (class_exists($payment) && method_exists($payment, 'getIntent')) {
                        // Use adjusted amount for payment if admin funded, otherwise use original amount
                        // $request->amount remains unchanged for frontend compatibility
                        $paymentAmount = ($adjustment && $adjustment['is_admin_funded']) 
                            ? $adjustment['adjusted_amount'] 
                            : $request->amount;
                        
                        $wallet['total'] = $paymentAmount;
                        $request->merge([
                            'type' => 'wallet',
                            'request_type' => 'api',
                        ]);
                        
                        return $payment::getIntent($wallet, $request);

                    } else {
                        throw new Exception(__('static.wallet.payment_module_not_found'), 400);
                    }
                }
            }

            throw new Exception(__('static.wallet.invalid_payment_method'), 400);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
