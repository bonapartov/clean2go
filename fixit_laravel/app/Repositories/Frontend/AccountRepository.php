<?php

namespace App\Repositories\Frontend;

use App\Enums\PaymentMethod;
use App\Enums\RoleEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Traits\WalletPointsTrait;
use App\Http\Traits\WalletBonusTrait;
use App\Models\ServiceRequest;
use Exception;
use App\Models\User;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Nwidart\Modules\Facades\Module;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Models\Category;

class AccountRepository extends BaseRepository
{
    use WalletPointsTrait;
    use WalletBonusTrait;

    protected $address;

    public function model()
    {
        $this->address = new Address();
        $this->service_request = new ServiceRequest();
        return User::class;
    }

    public function updateProfile($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->model->findOrFail(auth()->user()->id);
            $user->update([
                'name' => $request['name'],
                'email' => $request['email'],
                'phone' => (string) $request['phone'],
                'code' => $request['code'],
                'status' => true,
            ]);

            if (isset($request['image'])) {
                $user->clearMediaCollection('image');
                $user->addMediaFromRequest('image')->toMediaCollection('image');
            }

            DB::commit();
            return back()->with('message', 'Profile Updated Successfully.');
        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function updatePassword($request)
    {
        DB::beginTransaction();
        try {
            $this->model->findOrFail(auth()->user()->id)
                ->update(['password' => Hash::make($request->new_password)]);

            DB::commit();
            return back()->with('message', 'Password Updated Successfully.');
        } catch (Exception $e) {
            DB::rollback();

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function wallet($dataTable)
    {
        // Get active wallet bonuses for display
        // Always fetch bonuses to show offers, regardless of wallet bonus feature setting
        $walletBonuses = collect([]);
        $locale = app()->getLocale();
        $walletBonuses = \App\Models\WalletBonus::where('status', 1)
            ->orderBy('min_top_up_amount', 'asc')
            ->get()
            ->map(function ($bonus) use ($locale) {
                try {
                    $name = $bonus->getTranslation('name', $locale, false);
                    if (!$name && isset($bonus->name)) {
                        $name = is_array($bonus->name) ? ($bonus->name[$locale] ?? reset($bonus->name)) : $bonus->name;
                    }
                    
                    $description = $bonus->getTranslation('description', $locale, false);
                    if (!$description && isset($bonus->description)) {
                        $description = is_array($bonus->description) ? ($bonus->description[$locale] ?? reset($bonus->description)) : $bonus->description;
                    }
                } catch (\Exception $e) {
                    $name = $bonus->name ?? 'Special Offer';
                    $description = $bonus->description ?? '';
                }
                
                return [
                    'id' => $bonus->id,
                    'name' => $name ?? 'Special Offer',
                    'description' => $description ?? '',
                    'type' => $bonus->type,
                    'bonus' => $bonus->bonus,
                    'min_top_up_amount' => $bonus->min_top_up_amount,
                    'max_bonus' => $bonus->max_bonus,
                    'is_admin_funded' => $bonus->is_admin_funded,
                ];
            });
        
        return $dataTable->render('frontend.account.wallet', compact('walletBonuses'));
    }

    public function markAsRead($request)
    {
        $user = Auth::user();
        foreach ($user->unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        return response()->json(['status' => 'success']);
    }

    public function webMarkAsRead($request)
    {
        $user = Auth::user();
        foreach ($user->unreadNotifications as $notification) {
            $notification->markAsRead();
        }

        return redirect()?->back();
    }

    public function walletTopUp($request)
    {
        $user_id = Helpers::getCurrentUserId();
        $roleName = Helpers::getCurrentRoleName();
        if ($roleName === RoleEnum::CONSUMER) {
            $wallet = $this->getWallet($user_id);

            // Calculate adjusted amount if bonus is admin funded
            // Keep $request->amount unchanged for frontend compatibility
            $originalAmount = $request->amount;
            $adjustment = $this->calculateAdjustedTopUpAmount($originalAmount);
            
            // Store original amount and bonus info in request for later use in paymentStatus
            $request->merge([
                'original_topup_amount' => $originalAmount,
                'wallet_bonus_amount' => $adjustment['bonus_amount'] ?? 0,
                'wallet_bonus_id' => $adjustment['wallet_bonus']->id ?? null,
                'is_admin_funded' => $adjustment['is_admin_funded'] ?? false,
            ]);

            $payment = $this->createPayment($wallet, $request, $adjustment);
            if (isset($payment['is_redirect'])) {
                if ($payment['is_redirect']) {
                    return redirect()->away($payment['url']);
                }
            }
        }
        DB::beginTransaction();

        try {
            DB::commit();
            throw new Exception(__('static.wallet.permission_denied'), 403);
        } catch (Exception $e) {
            DB::rollback();

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getCustomJobs($request)
    {

        $userId = auth()->id();

        $serviceRequests = $this->service_request->where('deleted_at' , null)->where('user_id', $userId)->get();
        $serviceCategories = Category::getDropdownOptions();

        return view('frontend.account.custom-job',['serviceRequests' => $serviceRequests ,  'serviceCategories' => $serviceCategories]);

    }

    public function createPayment($wallet, $request, $adjustment = null)
    {
        try {

            if ($wallet) {
                $module = Module::find($request->payment_method);
                $request->merge(['type' => PaymentMethod::WALLET]);
                $request->merge(['request_type' => 'web']);
                if (! is_null($module) && $module?->isEnabled()) {
                    $moduleName = $module->getName();
                    $payment = 'Modules\\' . $moduleName . '\\Payment\\' . $moduleName;
                    if (class_exists($payment) && method_exists($payment, 'getIntent')) {
                        // Use adjusted amount for payment if admin funded, otherwise use original amount
                        // $request->amount remains unchanged for frontend compatibility
                        $paymentAmount = ($adjustment && $adjustment['is_admin_funded']) 
                            ? $adjustment['adjusted_amount'] 
                            : $request->amount;
                        
                        $wallet['total'] = $paymentAmount;

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

    public function referral($dataTable)
    {
        return $dataTable->render('frontend.account.referral');
    }

}
