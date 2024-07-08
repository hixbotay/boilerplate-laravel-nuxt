<?php

namespace App\Http\Controllers;

use App\Http\Enums\CurrencySymbolPosition;
use App\Http\Enums\EnumCheckoutType;
use App\Http\Enums\EnumFailOverRouting;
use App\Http\Enums\EnumGatewayRouting;
use App\Http\Enums\EnumPaymentOption;
use App\Http\Enums\EnumShipmentCarrier;
use App\Http\Enums\EnumShipmentStatus;
use App\Http\Enums\FulfillmentStatus;
use App\Http\Enums\OrderStatus;
use App\Http\Enums\OrderType;
use App\Http\Enums\PayStatus;
use App\Http\Enums\PlanPeriod;
use App\Http\Enums\PlanType;
use App\Http\Enums\PlatformType;
use App\Http\Enums\ServiceType;
use App\Http\Enums\SignatureProofType;
use App\Http\Enums\UserBusinessCategory;
use App\Http\Enums\UserBusinessType;
use App\Http\Enums\UserRoleType;
use App\Jobs\ValidateWhatsappNumberJob;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Permissions;
use App\Models\Role;
use App\Services\Cin;
use App\Services\Gstin;
use App\Services\Ip;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\LogHelper;
use App\Http\Enums\CampaignStatus;
use App\Http\Enums\ConfirmLinkStatus;
use App\Http\Enums\CreditTransactionType;
use App\Http\Enums\EnumEmailTemplate;
use App\Http\Enums\EnumFrontField;
use App\Http\Enums\ModuleType;
use App\Http\Enums\NotificationEventName;
use App\Http\Enums\NotificationMessageType;
use App\Http\Enums\NotificationService;
use App\Http\Enums\NotificationStatus;
use App\Jobs\SyncPaidOrderFromEcwidStoreJob;
use App\Models\Config;
use App\Models\Product;
use App\Models\ProductCategory;
use Throwable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class CommonController extends Controller
{
    /**
     * Get all routes and insert permissions
     *
     * @return \Illuminate\Http\Response
     */
    public function generatePermissions(Request $request)
    {
        $routes = (array) Route::getRoutes()->getRoutes();

        $definedPermissions = [];
        foreach ($routes as $route) {
            // print($route->action['as']);
            if (isset($route->action) && isset($route->action['as'])) {
                $position = strpos($route->action['as'], 'ignition');
                if ($position === false) array_push($definedPermissions, $route->action['as']);
            }
        }

        $existedPermissions = [];
        $permissions = Permissions::get();
        foreach ($permissions as $permission) {
            array_push($existedPermissions, $permission->name);
        }

        $newPermissions = array_diff($definedPermissions, $existedPermissions);

        $data = [];
        if (count($newPermissions)) {
            foreach ($newPermissions as $permissionName) {
                $tmp = explode('.', $permissionName);

                array_push($data, [
                    'name' => $permissionName,
                    'owner' => $tmp[0],
                    'resource' => $tmp[1],
                    'action' => $tmp[2],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            Permissions::insert($data);
        }

        return response()->json(["routes" => $data]);
    }

    /**
     * Get all permissions
     */
    public function getPermissions(Request $request)
    {

        $permissions = Permissions::all();

        return response()->json(["records" => $permissions]);
    }

    /**
     * Get admin config
     */
    public function getAdminConfig(Request $request)
    {
        // public config
        $config = [
            'trial_days' => env('TRIAL_DAYS'),
            'roles' => Role::select(['id', 'name', 'type'])->get(),
            'role_types' => UserRoleType::getAllConstant(),
            'order_statuses' => OrderStatus::getAllConstant(),
            'pay_statuses' => PayStatus::getAllConstant(),
        ];

        // admin config
        $adminConfigs = Config::all();
        foreach ($adminConfigs as $option) {
            $config[$option->name] = json_decode($option->value);
        }

        return response()->json(["config" => $config]);
    }

    /**
     * Get app public config
     */
    public function getConfig(Request $request)
    {
        $config = [
            'role_types' => UserRoleType::getAllConstant(),
            'admin_notify' => Config::getOptions('admin_notify')['value'],
            'home_page' => Config::getOptions('home_page')['value'],
        ];
        return response()->json($config);
    }

    public function saveAdminConfig(Request $request)
    {
        $body = $request->all();
        $body['value'] = json_encode($body['value']);
        $rules = [
            'name' => 'required|string',
            'value' => 'required|string',
        ];
        
        $validator = Validator::make($body, $rules);
        
        if ($validator->fails()) {
            return response()->json(["message" => $validator->errors()->first()], 400);
        }

        $record = Config::where('name', $body['name'])->first();

        if ($record) {
            $record->update(['value' => $body['value']]);
        } else {
            Config::create([
                'name' => $body['name'],
                'value' => $body['value']
            ]);
        }

        // get config again
        // public config
        $config = [
            'trial_days' => env('TRIAL_DAYS'),
            'roles' => Role::select(['id', 'name', 'type'])->get(),
            'role_types' => UserRoleType::getAllConstant(),
            'order_statuses' => OrderStatus::getAllConstant(),
            'pay_statuses' => PayStatus::getAllConstant(),
        ];

        // admin config
        $adminConfigs = Config::all();
        foreach ($adminConfigs as $option) {
            $config[$option->name] = json_decode($option->value);
        }

        return response()->json(["config" => $config]);
    }


    public function getUploadedFile(Request $request)
    {
        $relativePath = $request->path;

        $file = Storage::readStream($relativePath);

        return response()->stream(function () use ($file) {
            fpassthru($file);
        }, 200, [
            "Content-Type" => Storage::getMimetype($relativePath),
            "Content-Length" => Storage::getSize($relativePath)
        ]);
    }

    public function saveUploadFile(Request $request)
    {
        if ($request->hasFile('file')) {
            if (is_array($request->file('file'))) {
                $paths = [];

                foreach ($request->file('file') as $file) {
                    $fileName = str_replace(' ', '_', $file->getClientOriginalName());
                    $path = $file->storeAs('uploads/user_' . $request->user()->id, time() . '_' . $fileName);
                    array_push($paths, $path);
                }

                return response()->json(['path' => $paths]);
            }

            $fileName = str_replace(' ', '_', $request->file('file')->getClientOriginalName());
            $path = $request->file('file')->storeAs('uploads/user_' . $request->user()->id, time() . '_' . $fileName);
            return response()->json(['path' => $path]);
        }

        return response()->json(['message' => 'Invalid file'], 400);
    }

    public function deleteFile($path)
    {
        $delete =  Storage::delete($path);
        return response()->json([
            'path' => $delete
        ]);
    }

    public function getHomePage(Request $request){
        $config = Config::getOptions('home_page',[]);
        $catIds = [];
        $maxLimit = 1;
        foreach($config as $k=>$v){
            if($v['type'] == EnumFrontField::CATEGORY['value']){
                $catIds[] = $v['id'];
                if($maxLimit < $v['limit']){
                    $maxLimit = $v['limit'];
                }
            }
        }
        $productWCat = Product::with('categories')->getCategory($catIds)->get()->toArray();
        $products = [];
        foreach($productWCat as $prod){
            $cats = $prod['categories'];
            unset($prod['categories']);
            foreach($cats as $cat){
                $products[$cat['id']][] = $prod;
            }
        }
        foreach($config as $k=>&$v){
            if($v['type'] == EnumFrontField::CATEGORY['value']){
                $v['products'] = $products[$v['id']] ? array_slice($products[$v['id']],0,$v['limit']) : [];
            }
        }
        return response()->json([
            'records' => $config
        ]);
    }


}
