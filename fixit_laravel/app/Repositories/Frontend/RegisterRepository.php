<?php

namespace App\Repositories\Frontend;

use App\Events\CreateUserEvent;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Traits\ReferralTrait;
use App\Models\Address;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Prettus\Repository\Eloquent\BaseRepository;
use Spatie\Permission\Models\Role;

class RegisterRepository extends BaseRepository
{
    use ReferralTrait;
    
    protected $role;

    protected $address;

    public function model()
    {
        $this->address = new Address();
        $this->role = new Role();

        return User::class;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {
            $user = $this->model->create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => true,
                'referral_code' => Helpers::getReferralCodeByName($request->name, 6),
            ]);

            $role = Role::findByName('user');
            $user->assignRole($role);
            event(new CreateUserEvent($user));

            if (Helpers::walletIsEnable()) {
                $user->wallet()?->create();
                $user->wallet;
            }

            if ($request->referral_code) {
                $this->applyReferralCode($request->referral_code, $user, 'user');
            }
            DB::commit();

            $guestManualAddress = session('guest_manual_address');
            session()->forget('guest_manual_address');

            Auth::login($user);

            if ($guestManualAddress) {
                return to_route('frontend.account.address')->with('prefill_address', $guestManualAddress);
            }

            return to_route('frontend.home');

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }


    public function updatePassword($request, $id)
    {
        try {
            $this->model->findOrFail($id)->update([
                'password' => Hash::make($request->new_password),
            ]);

            return back()->with('message', 'User Password Update Successfully.');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
