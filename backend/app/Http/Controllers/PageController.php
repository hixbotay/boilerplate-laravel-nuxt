<?php

namespace App\Http\Controllers;

use App\Http\Enums\EnumFrontField;
use App\Http\Enums\OrderStatus;
use App\Http\Enums\PayStatus;
use App\Http\Enums\UserRoleType;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Permissions;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\Config;
use App\Models\Product;
use App\Models\ProductCategory;

class PageController extends Controller
{
    /**
     * Get all routes and insert permissions
     *
     * @return \Illuminate\Http\Response
     */
    public function getHomePage(Request $request)
    {
        $homePage = Config::getOptions('home_page')['value'];
        $res = [];
        $catId = [];
        foreach($homePage as $item){
            if($item['type'] == EnumFrontField::CATEGORY['value']){
                $catId[] = $item['id'];
            }
        }
        if(count($catId)){
            $catData = ProductCategory::find($catId);
            $productData = Product::get
        }
        return response()->json(["routes" => $data]);
    }
}
