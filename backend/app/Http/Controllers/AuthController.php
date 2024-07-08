<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Http\Enums\EnumEmailTemplate;
use App\Http\Enums\EnumPaymentType;
use App\Http\Enums\EnumSubscriptionStatus;
use App\Http\Enums\FulfillmentStatus;
use App\Http\Enums\OtpType;
use App\Http\Enums\ServiceType;
use App\Http\Enums\SignatureProofType;
use App\Http\Enums\UserBusinessCategory;
use App\Http\Enums\UserBusinessType;
use App\Http\Enums\UserRoleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Modules\Notify\Notify;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\Country;
use App\Models\CountryStates;
use App\Models\UserRole;
use App\Models\UserKycInformation;
use App\Models\Role;
use App\Models\Otp;
use App\Models\Plan;
use App\Models\Subscription;
use App\Notifications\EmailResetPassword;
use DateTime;
use App\Notifications\EmailVerification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Utilities\Common;
use App\Http\Enums\OrderType;
use App\Http\Enums\PayStatus;
use App\Models\EmailTemplate;
use App\Models\MerchantOrder;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(Request $request)
    {
      
        $rules = [
            'email' => 'required|email:rfc',
            'password' => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 400);
        }

        $credentials = request(['email', 'password']);
        $user = User::where([
            ['email', '=', $credentials['email']],
            ['status', '!=', 0]
            ])->first();

        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $role = UserRole::where('user_id', $user->id)->first();
        if ($role) $user->role = $role->role;

        $token = auth()->tokenById($user->id);

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function register(Request $request)
    {
        // only merchant can register for now (partner is later)

        $rules = [
            'email' => 'required|email:rfc|unique:users',
            'password' => 'required|string|min:6',
            'full_name' => 'required|string',
            'mobile' => 'required|max:15|unique:users,mobile',
        ];

        $data = $request->validate($rules);

        $data['password'] = Hash::make($request->get('password'));
        $data['status'] = 1;

        DB::beginTransaction();
        $user = User::create($data);

        // set role merchant
        UserRole::create(['role_id' => Role::getByType(UserRoleType::MERCHANT['value'])->first()->id, 'user_id' => $user->id]);

        $token = auth()->tokenById($user->id);
        DB::commit();
        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $credentials = request(['id', 'email']);

        $user = User::where(['id' => $credentials['id'], 'email' => $credentials['email']])->first();

        if (!$user) return response()->json(['message' => 'Wrong credentials'], 400);

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email is verified ealier'], 422);
        } else {
            $user->email_verified_at = now();
            $user->save();

            return response()->json(['message' => 'Success'], 200);
        }
    }

    public function getAuthUser(Request $request)
    {
        $user = auth()->user();
        $user['role'] = $user->getRole();
        // renew token
        $token = auth()->tokenById($user->id);

        return response()->json([
            'user' => $user,
            'access_token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $rules = [
            'email' => 'required|email'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 400);
        }

        $email = $request->get('email');

        $user = User::where(['email' => $email, 'status' => 1])->first();

        if ($user) {
            $token = Str::random(40);

            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => now()
            ]);

            //email details
            $details = [
                'greeting' => 'Hi ' . $user->full_name,
                'body' => 'To reset your Sellonboard password, please clicking below url.',
                'reset_password_url' => env('APP_RESET_PASSWORD_URL') . '?token=' . $token,
            ];

            //send email includes reset password url to user email
            Notification::send($user, new EmailResetPassword($details));

            return response()->json(['message' => "Success"]);
        }

        return response()->json(['message' => "User doesn't exist"], 404);
    }

    public function resetPassword(Request $request)
    {
        $rules = [
            'token' => 'required|string',
            'password' => 'required|string|min:6'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 400);
        }

        $data = request(['token', 'password']);

        $record = DB::table('password_resets')->where('token', $data['token'])->first();

        if ($record) {
            $user = User::where('email', $record->email)->first();

            if ($user) {
                $user->password = Hash::make($data['password']);
                $user->save();

                DB::table('password_resets')->where('token', $data['token'])->delete();

                return response()->json(['message' => 'Success']);
            }

            return response()->json(['message' => "User doesn't exist"], 404);
        }

        return response()->json(['message' => "Invalid token"], 400);
    }

    public function sendOtpViaEmail(Request $request)
    {
        try {
            $user = auth()->user();

            //email details
            $details = [
                'user_full_name' => $user->full_name,
                'user_email' => $user->email
            ];

            $now = Carbon::now();

            $existOtp = Otp::where([
                ['receiver', '=', $user->email],
                ['type', '=', OtpType::EMAIL['value']],
                ['expired_date', '>', $now->toDateTimeString()]
            ])->first();

            if ($existOtp) {
                $lastSentAt = new Carbon($existOtp->last_sent_at);

                if ($now->floatDiffInRealSeconds($lastSentAt) > 30) {
                    $details['otp_code'] = $existOtp->otp_code;

                    $existOtp->update([
                        'last_sent_at' => $now->toDateTimeString()
                    ]);

                    //send email verify to user email
                    Notification::send($user, new EmailVerification($details));
                }
            } else {
                $now->addMinutes((int) env('OTP_EXPIRED_TIME_IN_MINUTES')); // modify now, add 10 minutes

                $emailOtpCode = Common::randomNumericString(6);
                $details['otp_code'] = $emailOtpCode;

                Otp::create([
                    'receiver' => $details['user_email'],
                    'type' => OtpType::EMAIL['value'],
                    'otp_code' => $emailOtpCode,
                    'expired_date' => $now->toDateTimeString(),
                    'service' => 'smtp'
                ]);

                //send email verify to user email
                Notification::send($user, new EmailVerification($details));
            }

            return response()->json(['message' => 'Success']);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    public function sendOtpViaMobile(Request $request)
    {
        try {
            $user = auth()->user();

            if ($user->mobile_verified_at) return response()->json(['message' => 'Verified earlier'], 400);

            $isSend = false;
            $isInsert = false;
            $mobileOtpCode = Common::randomNumericString(6);

            $now = Carbon::now();

            $existOtp = Otp::where([
                ['receiver', '=', $user->mobile],
                ['type', '=', OtpType::PHONE['value']],
                ['expired_date', '>', $now->toDateTimeString()]
            ])->first();

            if ($existOtp) {
                $lastSentAt = new Carbon($existOtp->last_sent_at);

                if ($now->floatDiffInRealSeconds($lastSentAt) > 30) {
                    $mobileOtpCode = $existOtp->otp_code;

                    $existOtp->update([
                        'last_sent_at' => $now->toDateTimeString()
                    ]);

                    $isSend = true;
                    $isInsert = false;
                }
            } else {
                $isSend = true;
                $isInsert = true;
            }

            // send otp code via mobile phone
            if ($isSend) {
                // detect if mobile is Indian number
                $userCountry = Country::findOrFail($user->country_id);
                $mobile = $user->mobile;
                if (strpos($mobile, '+') !== 0) $mobile = $userCountry->mobile_code . $mobile;
                if ($userCountry->code === 'IN') {
                    // Indian mobile phone number
                    $otpService = new Notify(ServiceType::NEXTEL['id'], [
                        'auth_token' => env('NEXTEL_AUTH_TOKEN')
                    ]);
                } else {
                    $otpService = new Notify(ServiceType::TWILIO['id'], [
                        'account_sid' => env('TWILIO_ACCOUNT_SID'),
                        'auth_token' => env('TWILIO_AUTH_TOKEN'),
                        'service_id' => env('TWILIO_SERVICE_ID')
                    ]);
                }

                $result = $otpService->sendOtpCode($user->full_name, $mobile, $mobileOtpCode);
                if ($isInsert) {
                    $expiredAt = Carbon::now();
                    $expiredAt->addMinutes((int) env('OTP_EXPIRED_TIME_IN_MINUTES'));

                    Otp::create([
                        'message_id' => $result['message_id'],
                        'type' => OtpType::PHONE['value'],
                        'receiver' => $user->mobile,
                        'otp_code' => $result['otp_code'],
                        'expired_date' => $expiredAt->toDateTimeString(),
                        'service' => $result['service'],
                        'status' => $result['status'],
                        'request_data' => $result['request_data'],
                        'response_data' => $result['response_data'],
                        'last_sent_at' => $now->toDateTimeString(),
                    ]);
                }

                if ($result['status']) return response()->json(['message' => 'Success']);

                return response()->json(['message' => 'Failed'], 400);
            }

            return response()->json(['message' => 'Success']);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $user = auth()->user();

            $rules = [
                'email_otp_code' => 'required|string|max:6',
                // 'mobile_otp_code' => 'required|string|max:6',
            ];
            // email can be verified earlier via sigin in with social services
            if ($user->email_verified_at) {
                $rules['email_otp_code'] = 'nullable|string|max:6';
            }
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(["message" => $validator->errors()->first()], 400);
            }

            $updateData = [];

            // if request includes email otp, then verify it
            if (isset($request->email_otp_code)) {
                $emailOtpRecord = Otp::where([
                    ['type', '=', OtpType::EMAIL['value']],
                    ['receiver', '=', $user->email],
                    ['otp_code', '=', $request->email_otp_code],
                    ['status', '=', 1],
                    ['expired_date', '>=', Carbon::now()]
                ])->first();

                if (!$emailOtpRecord) return response()->json(['message' => 'Email OTP code is wrong or expired'], 400);

                // remove otp records
                $emailOtpRecord->delete();

                $updateData['email_verified_at'] = Carbon::now();
            }

            // // verify mobile otp
            // $mobileOtpRecord = Otp::where([
            //     ['type', '=', OtpType::PHONE['value']],
            //     ['receiver', '=', $user->mobile],
            //     ['status', '=', 1],
            //     ['expired_date', '>=', Carbon::now()]
            // ])
            //     ->orderBy('created_at', 'desc')
            //     ->first();

            // if (!$mobileOtpRecord) return response()->json(['message' => 'Mobile OTP code is wrong or expired'], 400);
            // else {
            //     if ($mobileOtpRecord->service == 'test') {
            //         $otpService = new Notify('test', []);
            //     } else if ($mobileOtpRecord->service == 'nextel') {
            //         $otpService = new Notify(ServiceType::NEXTEL['id'], [
            //             'auth_token' => env('NEXTEL_AUTH_TOKEN')
            //         ]);
            //     } else if ($mobileOtpRecord->service == 'twilio') {
            //         $otpService = new Notify(ServiceType::TWILIO['id'], [
            //             'account_sid' => env('TWILIO_ACCOUNT_SID'),
            //             'auth_token' => env('TWILIO_AUTH_TOKEN'),
            //             'service_id' => env('TWILIO_SERVICE_ID')
            //         ]);
            //     }

            //     $userCountry = Country::findOrFail($user->country_id);
            //     $receiver = $userCountry->mobile_code . $mobileOtpRecord->receiver;
            //     $result = $otpService->verifyOtpCode($mobileOtpRecord->message_id, $request->mobile_otp_code, $receiver);

            //     if (!$result) return response()->json(['message' => 'Mobile OTP code is wrong or expired'], 400);

            //     // remove otp records
            //     $mobileOtpRecord->delete();

            //     $updateData['mobile_verified_at'] = Carbon::now();
            // }

            if (count($updateData)) {
                $updateData['status'] = 1;
                User::where('id', $user->id)->update($updateData);
            }

            /* auto subscribe trial plan */
            $plan = Plan::where('is_trial_plan', 1)->first();
            $expiryDate = Carbon::now()->addDays((int) env('TRIAL_DAYS'))->format('Y-m-d');
            // start transaction
            DB::beginTransaction();
            // create subscription
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_type' => 'monthly',
                'api_key' => Str::random(15),
                'api_secret' => Str::random(40),
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays((int) env('TRIAL_DAYS')),
                'status' => EnumSubscriptionStatus::ACTIVATED['value'],
                'amount' => 0,
                'is_auto_renew' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            // create order
            $orderData = [
                'store_id' => null,
                'type' => OrderType::BILLING['value'],
                'email' => $user->email,
                'phone' => $user->mobile,
                'total' =>  $plan->monthly_price, // it should be 0
                'subtotal' => $plan->monthly_price, // it should be 0
                'discount' => 0,
                'tax' => 0,
                'payment_status' => PayStatus::PAID['value'],
                'payment_method' => 'Free',
                'payment_type' => EnumPaymentType::FREE_TRIAL['value'],
                'fulfillment_status' => FulfillmentStatus::SHIPPED['value'],
                'shipping_method' => 'Free',
                'billing_person' => null, // no data
                'params' => ['plan' => $plan, 'subscription_type' => 'monthly'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'shipping_person_name' => $user->full_name,
                'shipping_person_mobile' => $user->mobile,
                'billing_person_name' => $user->full_name,
                'billing_person_mobile' => $user->mobile,
                'currency' => $plan->currency->code
            ];

            MerchantOrder::create($orderData);
            // commit transaction
            DB::commit();

            // send emails
            $emailData = (object) [
                'user_name' => $user->full_name,
                'user_email' => $user->email,
                'user_mobile' => $user->mobile,
                'plan_name' => $plan->name,
                'plan_type' => 'monthly',
                'plan_description' => $plan->description,
                'expiry_date' => $expiryDate,
                'days_left' => (int) env('TRIAL_DAYS')
            ];
            // send onboarding email
            $onboardingEmailTemplate = EmailTemplate::where([
                ['type', '=', EnumEmailTemplate::ONBOARDING_EMAIL['value']],
                ['status', '=', 1]
            ])->first();
            if ($onboardingEmailTemplate) {
                $onboardingEmailSubject = NotificationHelper::replaceData($emailData, $onboardingEmailTemplate->subject);
                $onboardingEmailContent = NotificationHelper::replaceData($emailData, $onboardingEmailTemplate->html_content);
    
                Mail::send([], [], function ($message) use ($emailData, $onboardingEmailSubject, $onboardingEmailContent) {
                    $message->to($emailData->user_email)
                        ->subject($onboardingEmailSubject)
                        ->setBody($onboardingEmailContent, 'text/html'); // for HTML rich messages
                });
            }
            // send trial plan is subscribed email
            $planSubscribedEmailTemplate = EmailTemplate::where([
                ['type', '=', EnumEmailTemplate::PLAN_SUBSCRIBED['value']],
                ['status', '=', 1]
            ])->first();
            if ($planSubscribedEmailTemplate) {
                $planSubscribedEmailSubject = NotificationHelper::replaceData($emailData, $planSubscribedEmailTemplate->subject);
                $planSubscribedEmailContent = NotificationHelper::replaceData($emailData, $planSubscribedEmailTemplate->html_content);
    
                Mail::send([], [], function ($message) use ($emailData, $planSubscribedEmailSubject, $planSubscribedEmailContent) {
                    $message->to($emailData->user_email)
                        ->subject($planSubscribedEmailSubject)
                        ->setBody($planSubscribedEmailContent, 'text/html'); // for HTML rich messages
                });
            }

            return response()->json(['message' => 'Success']);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    public function createKycInformation(Request $request)
    {
        $user = auth()->user();

        $data = $request->all();

        $rules = [
            'accept_payment' => 'required|in:0,1',
            'website_url' => 'nullable|string',
            'app_url' => 'nullable|string',
            'business_type' => 'required|in:' . implode(',', UserBusinessType::getAllValue()),
            'business_category' => 'required|in:' . implode(',', UserBusinessCategory::getAllValue()),
            'business_subcategory' => 'nullable|string',
            'business_description' => 'required|string',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 400);
        }

        // check if record existed
        $record = UserKycInformation::where('user_id', $user->id)->first();
        if ($record) {
            $record->update($data);
        } else {
            $data['user_id'] = $user->id;
            $record = UserKycInformation::create($data);
        }

        return response()->json($record);
    }

    public function updateKycInformation(Request $request)
    {
        $user = auth()->user();

        $record = UserKycInformation::where('user_id', $user->id)->first();

        if ($record) {
            $query = $request->query();
            $data = $request->post();

            $rules = [];

            if ($query['step'] === '0') {
                $rules = [
                    'accept_payment' => 'required|in:0,1',
                    'website_url' => 'nullable|string',
                    'app_url' => 'nullable|string',
                    'business_type' => 'required|in:' . implode(',', UserBusinessType::getAllValue()),
                    'business_category' => 'required|in:' . implode(',', UserBusinessCategory::getAllValue()),
                    'business_subcategory' => 'nullable|string',
                    'business_description' => 'required|string',
                ];
            } else if ($query['step'] === '1') {
                $rules = [
                    'gstin' => 'nullable|string',
                    'business_pan' => 'nullable|string',
                    'business_name' => 'nullable|string',
                    'cin' => 'nullable|string',
                    'authorised_signatory_pan' => 'nullable|string',
                    'pan_owner_name' => 'nullable|string',
                    'billing_label' => 'required|string',
                    'address' => 'required|string',
                    'pincode' => 'required|string',
                    'city' => 'required|string',
                    'state_id' => 'nullable|exists:country_states,id',
                    'country_id' => 'required|exists:countries,id',
                    'operational_address' => 'required|string',
                    'operational_pincode' => 'required|string',
                    'operational_city' => 'required|string',
                    'operational_state_id' => 'nullable|exists:country_states,id',
                    'operational_country_id' => 'required|exists:countries,id',
                ];
            } else if ($query['step'] === '2') {
                $rules = [
                    'beneficiary_name' => 'required|string',
                    'branch_ifsc_code' => 'required|string',
                    'bank_account_number' => 'required|string',
                ];
            } else if ($query['step'] === '3') {
                $rules = [
                    'authorized_signature_address_proof' => 'required|in:' . implode(',', SignatureProofType::getAllValue()),
                    'id_card_front_image' => 'required|string',
                    'id_card_back_image' => 'required|string',
                    'business_registration_proof' => 'required|string',
                    'company_pan' => 'required|string',
                    'other_document' => 'nullable|string',
                ];

                if ($record->business_type === UserBusinessType::UNREGISTERED['value']) {
                    $rules['business_registration_proof'] = 'nullable|string';
                    $rules['company_pan'] = 'nullable|string';
                }
                if ($record->business_type === UserBusinessType::PROPRIETORSHIP['value']) {
                    $rules['company_pan'] = 'nullable|string';
                }
                if (in_array($record->business_type, [UserBusinessType::NGO['value'], UserBusinessType::TRUST['value'], UserBusinessType::SOCIETY['value']])) {
                    $rules['other_document'] = 'required|string';
                }
            } else if ($query['step'] === '4') {
                $rules = [
                    'is_agreed_policy' => 'required|in:1'
                ];
            }

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(["message" => $validator->errors()->first()], 400);
            }

            $record->update($data);

            return response()->json($record);
        }

        return response()->json(["message" => "Not found"], 404);
    }

    public function getKycInformation(Request $request)
    {
        $user = auth()->user();

        $record = UserKycInformation::leftJoin('countries as c1', 'c1.id', '=', 'country_id')
            ->leftJoin('countries as c2', 'c2.id', '=', 'operational_country_id')
            ->leftJoin('country_states as cs1', 'cs1.id', '=', 'state_id')
            ->leftJoin('country_states as cs2', 'cs2.id', '=', 'operational_state_id')
            ->where('user_id', $user->id)
            ->select(
                'user_kyc_informations.*', 
                'c1.name as country_name',
                'c2.name as operational_country_name',
                'cs1.name as state_name',
                'cs2.name as operational_state_name',
            )
            ->first();

        if (!$record) $record = [];

        return response()->json(['kyc_information' => $record]);
    }

    public function redirectToFacebook()
    {
        try {
            $redirect_url = Socialite::with('facebook')->stateless()->redirect()->getTargetUrl();

            return response()->json(["redirect_url" => $redirect_url]);
        } catch (\Exception $exception) {
            return response()->json(["message" => $exception->getMessage()], 400);
        }
    }

    public function loginWithFacebook()
    {
        try {
            $facebookUser = Socialite::with('facebook')->stateless()->user();
            $existUser = User::where('email', $facebookUser->email)->first();

            if ($existUser) {
                if (!$existUser->facebook_user_id) {
                    $existUser->facebook_user_id = $facebookUser->id;
                    $existUser->email_verified_at = Carbon::now();
                    $existUser->save();
                }

                return redirect(env('APP_MERCHANT_DASHBOARD_URL') . '/setToken?access_token=' . auth()->tokenById($existUser->id));
            } else {
                $user = User::create([
                    'full_name' => $facebookUser->name,
                    'email' => $facebookUser->email,
                    'mobile' => '',
                    'password' => Hash::make($facebookUser->id),
                    'facebook_user_id' => $facebookUser->id,
                    'email_verified_at' => Carbon::now(),
                    'status' => 1
                ]);

                $merchantRole = Role::where('type', UserRoleType::MERCHANT['value'])->first();

                UserRole::create(['role_id' => $merchantRole->id, 'user_id' => $user->id]);

                /* auto subscribe trial plan */
                $plan = Plan::where('is_trial_plan', 1)->first();
                $expiryDate = Carbon::now()->addDays((int) env('TRIAL_DAYS'))->format('Y-m-d');
                // start transaction
                DB::beginTransaction();
                // create subscription
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'plan_type' => 'monthly',
                    'api_key' => Str::random(15),
                    'api_secret' => Str::random(40),
                    'start_date' => Carbon::now(),
                    'end_date' => $expiryDate,
                    'status' => EnumSubscriptionStatus::ACTIVATED['value'],
                    'amount' => 0,
                    'is_auto_renew' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                // create order
                $orderData = [
                    'store_id' => null,
                    'type' => OrderType::BILLING['value'],
                    'email' => $user->email,
                    'phone' => $user->mobile,
                    'total' =>  $plan->monthly_price, // it should be 0
                    'subtotal' => $plan->monthly_price, // it should be 0
                    'discount' => 0,
                    'tax' => 0,
                    'payment_status' => PayStatus::PAID['value'],
                    'payment_method' => 'Free',
                    'payment_type' => EnumPaymentType::FREE_TRIAL['value'],
                    'fulfillment_status' => FulfillmentStatus::SHIPPED['value'],
                    'shipping_method' => 'Free',
                    'billing_person' => null, // no data
                    'params' => ['plan' => $plan, 'subscription_type' => 'monthly'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'shipping_person_name' => $user->full_name,
                    'shipping_person_mobile' => $user->mobile,
                    'billing_person_name' => $user->full_name,
                    'billing_person_mobile' => $user->mobile,
                    'currency' => $plan->currency->code
                ];

                MerchantOrder::create($orderData);
                // commit transaction
                DB::commit();

                // send emails
                $emailData = (object) [
                    'user_name' => $user->full_name,
                    'user_email' => $user->email,
                    'user_mobile' => $user->mobile,
                    'plan_name' => $plan->name,
                    'plan_type' => 'monthly',
                    'plan_description' => $plan->description,
                    'expiry_date' => $expiryDate,
                    'days_left' => (int) env('TRIAL_DAYS')
                ];
                // send onboarding email
                $onboardingEmailTemplate = EmailTemplate::where([
                    ['type', '=', EnumEmailTemplate::ONBOARDING_EMAIL['value']],
                    ['status', '=', 1]
                ])->first();
                if ($onboardingEmailTemplate) {
                    $onboardingEmailSubject = NotificationHelper::replaceData($emailData, $onboardingEmailTemplate->subject);
                    $onboardingEmailContent = NotificationHelper::replaceData($emailData, $onboardingEmailTemplate->html_content);
        
                    Mail::send([], [], function ($message) use ($emailData, $onboardingEmailSubject, $onboardingEmailContent) {
                        $message->to($emailData->user_email)
                            ->subject($onboardingEmailSubject)
                            ->setBody($onboardingEmailContent, 'text/html'); // for HTML rich messages
                    });
                }
                // send trial plan is subscribed email
                $planSubscribedEmailTemplate = EmailTemplate::where([
                    ['type', '=', EnumEmailTemplate::PLAN_SUBSCRIBED['value']],
                    ['status', '=', 1]
                ])->first();
                if ($planSubscribedEmailTemplate) {
                    $planSubscribedEmailSubject = NotificationHelper::replaceData($emailData, $planSubscribedEmailTemplate->subject);
                    $planSubscribedEmailContent = NotificationHelper::replaceData($emailData, $planSubscribedEmailTemplate->html_content);
        
                    Mail::send([], [], function ($message) use ($emailData, $planSubscribedEmailSubject, $planSubscribedEmailContent) {
                        $message->to($emailData->user_email)
                            ->subject($planSubscribedEmailSubject)
                            ->setBody($planSubscribedEmailContent, 'text/html'); // for HTML rich messages
                    });
                }

                return redirect(env('APP_MERCHANT_DASHBOARD_URL') . '/setToken?access_token=' . auth()->tokenById($user->id));
            }
        } catch (\Exception $exception) {
            // return response()->json(["message" => $exception->getMessage()], 400);
            return redirect(env('APP_MERCHANT_DASHBOARD_URL') . '/signin?error=1');
        }
    }

    public function redirectToGoogle()
    {
        try {
            $redirect_url = Socialite::with('google')->stateless()->redirect()->getTargetUrl();

            return response()->json(["redirect_url" => $redirect_url]);
        } catch (\Exception $exception) {
            return response()->json(["message" => $exception->getMessage()], 400);
        }
    }

    public function loginWithGoogle()
    {
        try {
            $googleUser = Socialite::with('google')->stateless()->user();

            $existUser = User::where('email', $googleUser->email)->first();

            if ($existUser) {
                if (!$existUser->google_user_id) {
                    $existUser->google_user_id = $googleUser->id;
                    $existUser->email_verified_at = Carbon::now();
                    $existUser->save();
                }

                return redirect(env('APP_MERCHANT_DASHBOARD_URL') . '/setToken?access_token=' . auth()->tokenById($existUser->id));
            } else {
                $user = User::create([
                    'full_name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'mobile' => '',
                    'password' => Hash::make($googleUser->id),
                    'google_user_id' => $googleUser->id,
                    'email_verified_at' => Carbon::now(),
                    'status' => 1
                ]);

                $merchantRole = Role::where('type', UserRoleType::MERCHANT['value'])->first();

                UserRole::create(['role_id' => $merchantRole->id, 'user_id' => $user->id]);

                /* auto subscribe trial plan */
                $plan = Plan::where('is_trial_plan', 1)->first();
                $expiryDate = Carbon::now()->addDays((int) env('TRIAL_DAYS'))->format('Y-m-d');
                // start transaction
                DB::beginTransaction();
                // create subscription
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'plan_type' => 'monthly',
                    'api_key' => Str::random(15),
                    'api_secret' => Str::random(40),
                    'start_date' => Carbon::now(),
                    'end_date' => Carbon::now()->addDays((int) env('TRIAL_DAYS')),
                    'status' => EnumSubscriptionStatus::ACTIVATED['value'],
                    'amount' => 0,
                    'is_auto_renew' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                // create order
                $orderData = [
                    'store_id' => null,
                    'type' => OrderType::BILLING['value'],
                    'email' => $user->email,
                    'phone' => $user->mobile,
                    'total' =>  $plan->monthly_price, // it should be 0
                    'subtotal' => $plan->monthly_price, // it should be 0
                    'discount' => 0,
                    'tax' => 0,
                    'payment_status' => PayStatus::PAID['value'],
                    'payment_method' => 'Free',
                    'payment_type' => EnumPaymentType::FREE_TRIAL['value'],
                    'fulfillment_status' => FulfillmentStatus::SHIPPED['value'],
                    'shipping_method' => 'Free',
                    'billing_person' => null, // no data
                    'params' => ['plan' => $plan, 'subscription_type' => 'monthly'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'shipping_person_name' => $user->full_name,
                    'shipping_person_mobile' => $user->mobile,
                    'billing_person_name' => $user->full_name,
                    'billing_person_mobile' => $user->mobile,
                    'currency' => $plan->currency->code
                ];

                MerchantOrder::create($orderData);
                // commit transaction
                DB::commit();

                // send emails
                $emailData = (object) [
                    'user_name' => $user->full_name,
                    'user_email' => $user->email,
                    'user_mobile' => $user->mobile,
                    'plan_name' => $plan->name,
                    'plan_type' => 'monthly',
                    'plan_description' => $plan->description,
                    'expiry_date' => $expiryDate,
                    'days_left' => (int) env('TRIAL_DAYS')
                ];
                // send onboarding email
                $onboardingEmailTemplate = EmailTemplate::where([
                    ['type', '=', EnumEmailTemplate::ONBOARDING_EMAIL['value']],
                    ['status', '=', 1]
                ])->first();
                if ($onboardingEmailTemplate) {
                    $onboardingEmailSubject = NotificationHelper::replaceData($emailData, $onboardingEmailTemplate->subject);
                    $onboardingEmailContent = NotificationHelper::replaceData($emailData, $onboardingEmailTemplate->html_content);
        
                    Mail::send([], [], function ($message) use ($emailData, $onboardingEmailSubject, $onboardingEmailContent) {
                        $message->to($emailData->user_email)
                            ->subject($onboardingEmailSubject)
                            ->setBody($onboardingEmailContent, 'text/html'); // for HTML rich messages
                    });
                }
                // send trial plan is subscribed email
                $planSubscribedEmailTemplate = EmailTemplate::where([
                    ['type', '=', EnumEmailTemplate::PLAN_SUBSCRIBED['value']],
                    ['status', '=', 1]
                ])->first();
                if ($planSubscribedEmailTemplate) {
                    $planSubscribedEmailSubject = NotificationHelper::replaceData($emailData, $planSubscribedEmailTemplate->subject);
                    $planSubscribedEmailContent = NotificationHelper::replaceData($emailData, $planSubscribedEmailTemplate->html_content);
        
                    Mail::send([], [], function ($message) use ($emailData, $planSubscribedEmailSubject, $planSubscribedEmailContent) {
                        $message->to($emailData->user_email)
                            ->subject($planSubscribedEmailSubject)
                            ->setBody($planSubscribedEmailContent, 'text/html'); // for HTML rich messages
                    });
                }

                return redirect(env('APP_MERCHANT_DASHBOARD_URL') . '/setToken?access_token=' . auth()->tokenById($user->id));
            }
        } catch (\Exception $exception) {
            // return response()->json(["message" => $exception->getMessage()], 400);
            return redirect(env('APP_MERCHANT_DASHBOARD_URL') . '/signin?error=1');
        }
    }

    public function getAccountDetailProgress()
    {
        try {
            $user = auth()->user();

            $userWithKycInformation = User::leftJoin('user_kyc_informations', 'users.id', '=', 'user_id')
                ->leftJoin('countries', 'users.country_id', '=', 'countries.id')
                ->where('users.id', $user->id)
                ->select([
                    'users.email',
                    'users.mobile',
                    // 'users.email_verified_at',
                    // 'users.mobile_verified_at',
                    'users.full_name',
                    // 'users.facebook_user_id',
                    // 'users.google_user_id',
                    'user_kyc_informations.*',
                    'countries.code as country_code'
                ])
                ->first()
                ->toArray();

            if ($userWithKycInformation) {
                $countryCode = $userWithKycInformation['country_code'];

                // remove unimportant fields
                unset($userWithKycInformation['id']);
                unset($userWithKycInformation['user_id']);
                unset($userWithKycInformation['created_at']);
                unset($userWithKycInformation['updated_at']);
                unset($userWithKycInformation['role']);
                unset($userWithKycInformation['country_code']);
                unset($userWithKycInformation['parent']);
                unset($userWithKycInformation['business_subcategory']); // <== we don't count this field because some categories don't have any child
                unset($userWithKycInformation['app_url']); // <==  this field is optional, so don't need to calculate it

                // remove some fields if user is not Indian
                if ($countryCode != 'IN') {
                    unset($userWithKycInformation['gstin']);
                    unset($userWithKycInformation['business_pan']);
                    unset($userWithKycInformation['business_name']);
                    unset($userWithKycInformation['cin']);
                    unset($userWithKycInformation['authorised_signatory_pan']);
                    unset($userWithKycInformation['pan_owner_name']);
                    unset($userWithKycInformation['beneficiary_name']);
                    unset($userWithKycInformation['branch_ifsc_code']);
                    unset($userWithKycInformation['bank_account_number']);
                    unset($userWithKycInformation['authorized_signature_address_proof']);
                    unset($userWithKycInformation['id_card_front_image']);
                    unset($userWithKycInformation['id_card_back_image']);
                    unset($userWithKycInformation['business_registration_proof']);
                    unset($userWithKycInformation['company_pan']);
                    unset($userWithKycInformation['other_document']);
                }

                $availableStatesCount = CountryStates::where('country_id', $userWithKycInformation->country_id)->count();
                if (!$availableStatesCount) {
                    unset($userWithKycInformation['state_id']);
                    unset($userWithKycInformation['operational_state_id']);
                }

                $total = 0;
                $unfilledCount = 0;
                foreach ($userWithKycInformation as $key => $value) {
                    $total++;
                    if ($value === null || $value === '') $unfilledCount++;
                }

                $completedPercentage = round((1 - $unfilledCount / $total) * 100);

                return response()->json(['completed_percentage' => $completedPercentage, 'total' => $total, 'unfilled' => $unfilledCount, 'kyc' => $userWithKycInformation]);
            }

            return response()->json(['message' => 'Not found'], 404);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    public function updateAuthUser(Request $request)
    {
        try {
            $user = auth()->user();

            $payload = $request->validate([
                'full_name' => 'string',
                'country_id' => 'exists:countries,id',
                'mobile' => 'max:15|unique:users,mobile,' . $user->id, // unique with different user id
                'coupon_code' => 'string|nullable',
                'is_receive_updates_via_whatsapp' => 'in:0,1'
            ]);

            $user = User::find($user->id);
            $user->update($payload);

            return response()->json(['user' => $user]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 400);
        }
    }

    public function checkEmailExisted(Request $request)
    {
        try {
            $rules = [
                'email' => 'required|email:rfc'
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(["message" => $validator->errors()->first()], 400);
            }

            $user = User::where('email', $request->email)->first();

            if ($user) return response()->json(['is_existed' => true]);
            return response()->json(['is_existed' => false]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function getSubscription(Request $request)
    {
        try {
            $user = auth()->user();

            $now = date('Y-m-d');
            $subscription = Subscription::join('plans', 'plans.id', '=', 'plan_id')
                ->where('user_id', $user->id)
                ->where('subscriptions.status', EnumSubscriptionStatus::ACTIVATED['value'])
                ->whereDate('start_date', '<=', $now)
                ->whereDate('end_date', '>=', $now)
                ->select('subscriptions.*', 'plans.name as plan_name', 'plans.monthly_price', 'plans.monthly_price_paid_annual', 'plans.is_trial_plan as is_trial_plan')
                ->orderBy('start_date', 'DESC')
                ->first();
            $pendingSubscription = Subscription::join('plans', 'plans.id', '=', 'plan_id')
                ->where('user_id', $user->id)
                ->where('subscriptions.status', EnumSubscriptionStatus::PENDING['value'])
                ->whereDate('start_date', '>=', $now)
                ->select('subscriptions.*', 'plans.name as plan_name', 'plans.monthly_price', 'plans.monthly_price_paid_annual', 'plans.is_trial_plan as is_trial_plan')
                ->orderBy('start_date', 'ASC')
                ->first();

            return response()->json(['subscription' => $subscription, 'pending' => $pendingSubscription]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
