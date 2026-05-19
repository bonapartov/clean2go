<?php

namespace App\Http\Controllers\API;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use App\Mail\ForgotPassword;
use Illuminate\Http\Request;
use App\Events\CreateUserEvent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Events\CreateProviderEvent;
use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ReferralTrait;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\API\SocialLoginRequest;


class AuthController extends Controller
{
    use ReferralTrait;

    public function register(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'required|required|min:6|unique:users',
                'code' => 'required',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required|',
                'fcm_token' => 'required|string',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'fcm_token' => $request->fcm_token,
                'password' => Hash::make($request->password),
                'code' => $request->code,
                'phone' => $request->phone,
                'status' => true,
                'referral_code' => Helpers::getReferralCodeByName($request->name, 6),
            ]);

            $user->assignRole(RoleEnum::CONSUMER);

            // Simplified referral code validation for user-to-user referrals
            if ($request->referral_code) {
                $referrer = User::where('referral_code', $request->referral_code)
                    ->where('status', true)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$referrer) {
                    throw new Exception(__('validation.invalid_referral_code_or_referrer_not_found'), 422);
                }

                // Ensure referrer is also a user (user type validation)
                if (!$referrer->hasRole(RoleEnum::CONSUMER)) {
                    throw new Exception(__('validation.referral_code_belongs_to_a_different_user_type'), 422);
                }
            }
            event(new CreateUserEvent($user));
            DB::commit();

            // Apply referral code using simplified ReferralTrait for referral relationship creation
            if ($request->referral_code) {
                $this->applyReferralCode($request->referral_code, $user, 'user');
            }
            
            return [
                'access_token' => $user->createToken('auth_token')->plainTextToken,
                'success' => true,
            ];
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function registerProvider(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|string',
                'name' => 'required|string|max:255',
                'email' => ['required','string','email', Rule::unique('users', 'email')->whereNull('deleted_at')],
                'phone' => ['required','numeric', Rule::unique('users', 'phone')->whereNull('deleted_at')],
                'role' => 'required|in:provider,serviceman',
                'code' => 'required',
                'provider_id' => 'required_if:role,serviceman|exists:users,id,deleted_at,NULL',
                'zoneIds*' => 'required_if:role,provider,exists:zones,id,deleted_at,NULL',
                'password' => 'required|string|min:8|confirmed',
                'password_confirmation' => 'required',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $provider = User::create([
                'type' => $request->type,
                'name' => $request->name,
                'email' => $request->email,
                'fcm_token' => $request->fcm_token,
                'password' => Hash::make($request->password),
                'code' => $request->code,
                'phone' => $request->phone,
                'provider_id' => $request->provider_id,
                'status' => true,
                'referral_code' => Helpers::getReferralCodeByName($request->name, 6),
            ]);

            if($request->role == RoleEnum::PROVIDER){
                $provider->assignRole(RoleEnum::PROVIDER);
            } else {
                $provider->assignRole(RoleEnum::SERVICEMAN);
            }

            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image');
                $provider->addMedia($profileImage)->toMediaCollection('profile_image');
            }

            if (isset($request->zoneIds)) {
                $provider->zones()->attach($request->zoneIds);
                $provider->zones;
            }

            // Simplified referral code validation for provider-to-provider referrals
            if ($request->referral_code) {
                $referrer = User::where('referral_code', $request->referral_code)
                    ->where('status', true)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$referrer) {
                    throw new Exception(__('validation.invalid_referral_code_or_referrer_not_found'), 422);
                }

                // Ensure referrer is also a provider (provider type validation)
                if (!$referrer->hasRole(RoleEnum::PROVIDER)) {
                    throw new Exception(__('validation.referral_code_belongs_to_a_different_user_type'), 422);
                }
            }

            DB::commit();

            // Apply referral code using simplified ReferralTrait for referral relationship creation
            if ($request->referral_code) {
                $this->applyReferralCode($request->referral_code, $provider, 'provider');
            }
            
            event(new CreateProviderEvent($provider));
            return [
                'access_token' => $provider->createToken('auth_token')->plainTextToken,
                'type' => $request->type,
                'success' => true,
            ];
        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function login(Request $request)
    {
        try {
            $user = $this->verifyLogin($request);

            if (!Hash::check($request->password, $user->password)) {
                throw new Exception(__('passwords.incorrect_password'), 400);
            }

            if ($request->fcm_token) {
                $user->fcm_token = $request->fcm_token;
                $user->save();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'access_token' => $token,
                'success' => true,
            ];

        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }


    public function socialLogin(SocialLoginRequest $request)
    {
        $loginMethod = $request->input('login_type');
        $user = (object) $request->input('user');

        DB::beginTransaction();
        try {
            $user = $this->createOrGetUser($loginMethod, $user);
            if ($request->fcm_token) {
                $user->fcm_token = $request->fcm_token;
                $user->save();
            }

            DB::commit();
            if ($user->status) {
                return response()->json([
                    'success' => true,
                    'access_token' => $user->createToken('Sanctom')->plainTextToken,
                ], 200);
            }

            throw new Exception(__('auth.user_deactivated'), 403);
        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    private function createOrGetUser($loginMethod, $user)
    {
        if ($loginMethod === 'phone') {
            $phone = $user->phone;
            $code = $user->code;

            $existingUser = User::where('phone', $phone)->first();

            if ($existingUser) {
                return $existingUser;
            }

            $newUser = User::create([
                'status' => true,
                'phone' => $phone,
                'code' => $code,
            ]);

        } else {
            $email = $user->email;
            $name = $user->name;

            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                return $existingUser;
            }

            $newUser = User::create([
                'status' => true,
                'email' => $email ?? null,
                'name' => $name ?? null,
            ]);
        }

        $userRole = Role::where('name', RoleEnum::CONSUMER)->first();
        if ($userRole) {
            $newUser->assignRole($userRole);
        }

        return $newUser;

    }

    public function verifyLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->messages()->first(), 422);
        }

        $user = User::where([['email', $request->email], ['status', true]])->select('id', 'name', 'email', 'password', 'fcm_token')->first();
        
        if (!$user) {
            throw new Exception(__('validation.user_not_exists'), 400);
        }

        return $user;
    }

    public function logout(Request $request)
    {
        try {

            $token = PersonalAccessToken::findToken($request->bearerToken());
            if (!$token) {
                throw new Exception(__('auth.token_invalid'), 400);
            }

            return [
                'message' => __('auth.logged_out'),
                'success' => true,
            ];
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function forgotPassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'email|exists:users',
                'phone' => 'exists:users',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $otp = $this->generateOtp($request);
            if (isset($request->email)) {
                Mail::to($request->email)->send(new ForgotPassword($otp));
            }

            return [
                'message' => __('auth.sent_verification_code_msg'),
                'success' => true,
            ];

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function sendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'dial_code' => 'required',
                'phone' => 'required'
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $otp = rand(111111, 999999);
            $sendTo = ('+'.$request->dial_code.$request->phone);
            $message = "This is your otp:$otp";

            $sendOTP = Helpers::sendSMS($sendTo, $message);
            if(isset($sendOTP->account_sid)){
                DB::table('password_resets')->insert([
                    'otp' => $otp,
                    'phone' => $request->phone,
                    'created_at' => Carbon::now(),
                ]);
                return [
                    'message' => __('auth.otp_sent'),
                    'success' => true,
                ];
            } else {
                return [
                    'message' => $sendOTP->message ?? "Invalid Response",
                    'success' => false,
                ];
            }


        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verifySendOtp(Request $request)
    {
        try {
            if(Helpers::getDefaultSMSGateway() !== 'firebase') {
                $validator = Validator::make($request->all(), [
                    'otp' => 'required',
                    'phone' => 'required',
                ]);

                if ($validator->fails()) {
                    throw new Exception($validator->messages()->first(), 422);
                }

                $verify = DB::table('password_resets')
                    ->where('otp', $request->otp)
                    ->where('phone', $request->phone)
                    ->where('created_at', '>', Carbon::now()->subHours(1))
                    ->first();

                if (!$verify) {
                    throw new Exception(__('auth.invalid_otp_or_phone'), 400);
                }

                $user = User::firstOrCreate(['phone' => $verify->phone], [
                    'email' => $verify->email,
                    'code' => $request->code,
                    'status' => true,
                ]);

                if (!$user) {
                    throw new Exception(__('auth.user_not_exists'), 404);
                }

                if (!$user->status) {
                    throw new Exception(__('auth.user_inactive'), 400);
                }

                DB::table('password_resets')->where('otp', $request->otp)
                    ->where(function ($query) use ($request) {
                        $query->where('phone', $request->phone);
                    })
                    ->where('created_at', '>', Carbon::now()->subHours(1))
                    ->delete();

                DB::commit();

                return [
                    'access_token' => $user->createToken('auth_token')->plainTextToken,
                    'user' => $user,
                    'success' => true,
                ];

            } else {
                $validator = Validator::make($request->all(), [
                    'phone' => 'required|string',
                    'firebase_uid' => 'required|string',
                ]);

                if ($validator->fails()) {
                    throw new Exception($validator->messages()->first(), 422);
                }


                    $user = User::where('phone', $request->phone)->first();
                    if (!$user) {
                        throw new Exception(__('auth.user_not_exists'), 404);
                    }

                    if (empty($user->firebase_uid)) {
                        $user->firebase_uid = $request->firebase_uid;
                        $user->save();
                    }

                    return [
                        'access_token' => $user->createToken('auth_token')->plainTextToken,
                        'user' => $user,
                        'success' => true,
                    ];
            }


        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function generateOtp($request)
    {
        $otp = rand(111111, 999999);
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'otp' => $otp,
            'phone' => $request->phone,
            'created_at' => Carbon::now(),
        ]);

        return $otp;
    }

    public function updatePassword(Request $request)
    {
        DB::beginTransaction();
        try {

            $validator = Validator::make($request->all(), [
                'otp' => 'required',
                'email' => 'required|email|max:255|exists:users',
                'password' => 'required|min:8',
                'password_confirmation' => 'required|same:password',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $user = DB::table('password_resets')
                ->where('otp', $request->otp)
                ->where(function ($query) use ($request) {
                    $query->where('email', $request->email);
                })
                ->where('created_at', '>', Carbon::now()->subHours(1))
                ->first();

            if (!$user) {
                throw new Exception(__('auth.invalid_email_phone_or_token'), 400);
            }

            User::where(function ($query) use ($request) {
                $query->where('email', $request->email);
            })->update(['password' => Hash::make($request->password)]);

            DB::table('password_resets')->where('otp', $request->otp)
                ->where(function ($query) use ($request) {
                    $query->where('email', $request->email);
                })
                ->where('created_at', '>', Carbon::now()->subHours(1))
                ->delete();

            DB::commit();

            return [
                'message' => __('auth.password_has_been_changed'),
                'success' => true,
            ];

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verifyOtp(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'otp' => 'required',
                'email' => 'exists:users|email',
            ]);

            if ($validator->fails()) {
                throw new Exception($validator->messages()->first(), 422);
            }

            $verify = DB::table('password_resets')
                ->where('otp', $request->otp)
                ->where('email', $request->email)
                ->where('created_at', '>', Carbon::now()->subHours(1))
                ->first();

            if (!$verify) {
                throw new Exception(__('auth.invalid_otp_or_email'), 400);
            }

            $user = User::firstOrCreate(['email' => $verify->email], [
                'email' => $verify->email,
                'code' => $request->code,
                'status' => true,
            ]);

            if (!$user) {
                throw new Exception(__('auth.user_not_exists'), 404);
            }

            if (!$user->status) {
                throw new Exception(__('auth.user_inactive'), 400);
            }

            return [
                'access_token' => $user->createToken('auth_token')->plainTextToken,
                'user' => $user,
                'success' => true,
            ];

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
