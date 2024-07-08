<?php

namespace App\Http\Controllers;

use App\Http\Enums\EnumDomainStatus;
use App\Http\Enums\EnumPaymentSettlementStatus;
use App\Http\Enums\OrderType;
use App\Http\Enums\PayStatus;
use App\Http\Enums\UserBusinessCategory;
use App\Http\Enums\UserBusinessType;
use App\Http\Enums\ServiceType;
use App\Http\Enums\UserRoleType;
use App\Jobs\OnboardingServiceJob;
use App\Models\Country;
use App\Models\CountryStates;
use App\Models\MerchantOrder;
use App\Models\PaymentSettlement;
use App\Models\PaymentTransaction;
use App\Models\PayoutsHistory;
use App\Models\UserService;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserKycInformation;
use App\Models\Service;
use App\Modules\Common\Models\KYC;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserController extends Controller
{
    /**
     * Get a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $query = User::filter($request);
        $res = [
            'page' => $query->getPageNumber(),
            'per_page' => $query->getPerPage(),
        ];
        if ($request->role_id) {
            $query->getByRole($request->role_id);
        }
        if($request->is_paginate){
            $res['total_records'] = $query->getTotal();
        }else{
            $res['records'] = $query->get();
        }

        return response()->json($res);
    }

    /**
     * Creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $body = $request->validate([
            'full_name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|min:6',
            'mobile' => 'required|string',
            // 'country_id' => 'required|exists:countries,id',
            'parent_id' => 'exists:users,id',
            'role' => 'required|exists:roles,id',
            'partner_referral_code' => 'string'
        ]);
        
        $body['password'] = Hash::make($body['password']);
        $body['email_verified_at'] = Carbon::now();
        $body['mobile_verified_at'] = Carbon::now();

        $role_id = $body['role'];
        unset($body['role']);

        $user = User::create($body);

        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $role_id
        ]);

        return response()->json($user);
    }

    /**
     * Get the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function get($id)
    {
        $user = User::with('role')->where('id', $id)->first();
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate = [
            'full_name' => 'required|string',
            'mobile' => 'required|string',
            'parent_id' => 'exists:users,id',
            'status' => 'numeric',
        ];
        if (!empty($request->password)) {
            $validate['password'] = 'required|string|min:6';
        }
        if (!empty($request->role)) {
            $validate['role'] = 'required|exists:roles,id';
        }
        $body = $request->validate($validate);
        $user = User::findOrFail($id);
        
        if (isset($body['password'])) {
            $body['password'] = Hash::make($body['password']);
        }

        if (isset($body['role'])) {
            $role = $body['role'];
            unset($body['role']);

            UserRole::where('user_id', $id)->update(['role_id' => $role]);
        }

        $config = $user->config;
        $config['notification']['whatsapp']['settings']['chat_iframe'] = $body['whatsapp_chat_iframe'];
        $config['notification']['whatsapp']['settings']['bot_iframe'] = $body['whatsapp_bot_iframe'];
        $body['config'] = $config;
        unset($body['whatsapp_chat_iframe']);
        unset($body['whatsapp_bot_iframe']);

        $user->update($body);

        return response()->json($user);
    }

    public function summaryTrafficUser() {
        $listUserRegister = User::select(DB::raw('DATE(users.created_at) AS date'), DB::raw('COUNT(DATE(users.created_at)) AS count'))->groupBy('date')->get();
        $listUserRegister = $listUserRegister->map(function ($item, $key) {
            return array(
                'date' => $item['date'],
                'count' => $item['count']
            );
        });;
        return response()->json(['traffic_user' => $listUserRegister]);

    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $user = User::find($id);
            if (!empty($user)) {
                $user->delete();
                UserRole::where('user_id', $id)->first()->delete();
            } else {
                return response()->json(['message' => 'not exist'], 400);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(array('message' => $e->getMessage()), 400);
        }
        return response()->json(['message' => 'Delete success'], 202);
    }

    public function updateClient(Request $request) {
        $validate = [
            'full_name' => 'required|string',
            'mobile' => 'required|string',
        ];
        if (!empty($request->password)) {
            $validate['password'] = 'required|string|min:6';
        }
        $body = $request->validate($validate);
        
        $user = $request->user();
        if (isset($body['password'])) {
            $body['password'] = Hash::make($body['password']);
        }
        $user->update($body);

        return response()->json(['user' => $user]);
    }
    /**
     * Assign permissions to specific users
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assignPermissions(Request $request, $id)
    {
        $body = (array) json_decode($request->getContent());

        $rules = [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id'
        ];

        $validator = Validator::make($body, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 400);
        }

        $user = User::findOrFail($id);

        $user->permissions = $body['permission_ids'];
        $user->save();

        return response()->json($user);
    }

}
