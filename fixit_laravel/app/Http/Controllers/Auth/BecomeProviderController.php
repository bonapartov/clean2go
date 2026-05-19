<?php

namespace App\Http\Controllers\Auth;

use App\Enums\CategoryType;
use App\Enums\RoleEnum;
use App\Enums\UserTypeEnum;
use App\Events\CreateProviderEvent;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\BecomeProviderRequest;
use App\Http\Traits\ReferralTrait;
use App\Models\Address;
use App\Models\Category;
use App\Models\Company;
use App\Models\Role;
use App\Models\SeoSetting;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BecomeProviderController extends Controller
{
   use ReferralTrait;
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function index()
    {
        $categories = Category::where('category_type', CategoryType::SERVICE)->where('status', true)->pluck('title','id');
        $seoSetting = SeoSetting::where('page_slug', 'become-provider')->where('is_active', true)->first();
        return view('auth.register',[
            'categories' => $categories,
            'seoSetting' => $seoSetting
        ]);
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @return \App\Models\User
     */
    protected function store(BecomeProviderRequest $request)
    {
        DB::beginTransaction();
        try {

            $data = $request?->all();
            if($data['role'] === 'serviceman'){
                $provider_id = $data['provider_id'];
            } else {
                $provider_id = null;
            }

            $settings = Helpers::getSettings();
            $autoApprove = $settings['activation']['provider_auto_approve'] ?? 0;
            
            // Provider creation
            $provider = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'code' => $data['code'],
                'type' => $data['type'],
                'provider_id' => $provider_id,
                'password' => Hash::make($data['password']),
                'status' => $autoApprove == 1 ? 1 : 0,
                'referral_code' => Helpers::getReferralCodeByName($request->name, 6),
            ]);

            // Assign role
            if($data['role'] === RoleEnum::PROVIDER){
                $role = Role::where('name', RoleEnum::PROVIDER)->first();
                $provider->assignRole($role);
            } else {
                $role = Role::where('name', RoleEnum::SERVICEMAN)->first();
                $provider->assignRole($role);
            }

            // Handle known languages
            if (isset($data['known_languages'])) {
                $provider->knownLanguages()->attach($data['known_languages']);
            }

            // Handle company logo image
            if (isset($data['image']) && $data['image']?->isValid()) {
                $provider->addMedia($data['image'])->toMediaCollection('image');
            }

            // Handle zones
            if (isset($data['zones'])) {
                $provider->zones()->attach($data['zones']);
            }

            if ($data['role'] === RoleEnum::PROVIDER) {
                event(new CreateProviderEvent($provider));
            }

            DB::commit();
            if ($request->referral_code) {
                $this->applyReferralCode($request->referral_code, $provider, 'provider');
            }
            return redirect()->route('backend.dashboard')->with('message', 'Provider Registered Successfully.');

        } catch (Exception $e) {
            DB::rollback();
            return back()->with('error', $e->getMessage());
        }
    }

    public function getProvidersByCategory(Request $request)
    {
        $categoryId = $request->category_id;
        $providers = User::whereHas('services.categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        })->pluck('name', 'id');

        return response()->json(['providers' => $providers]);
    }
}
