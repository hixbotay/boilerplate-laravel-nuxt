<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
// controllers
use App\Http\Controllers\CommonController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CountryStateController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PaymentTransactionController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingController;

/**
 * Set name with authenticated routes only
 */

Route::group(['prefix' => 'admin', 'middleware' => [ 'assign.guard', 'jwt.auth', 'verify.status', 'json'], 'as' => 'admin.'], function () {

    // config
    Route::apiResource('/setting', SettingController::class);
    Route::prefix('config')->group(function () {
        Route::get('/', [CommonController::class, 'getAdminConfig'])->name('config.list');
        Route::post('/', [CommonController::class, 'saveAdminConfig'])->name('config.save');
    });

    // users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'list'])->name('users.list');
        Route::post('/', [UserController::class, 'create'])->name('users.create');
        Route::get('/{id}', [UserController::class, 'get'])->name('users.get');
        Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.delete');

        // Route::put('/{id}/permissions', [UserController::class, 'assignPermissions'])->name('users.assign_permissions');
        // Route::post('/bulk', [UserController::class, 'createBulkUsers'])->name('users.create_bulk');
        // Route::post('/assign', [UserController::class, 'assignUsersToParent'])->name('users.assign');
        // Route::post('/unassign', [UserController::class, 'unassignUsersFromParent'])->name('users.unassign');
        // Route::post('/unassign', [UserController::class, 'unassignUsersFromParent'])->name('users.unassign');
        // Route::post('/{id}/access-token', [UserController::class, 'fetchAccessTokenOfUser'])->name('users.fetch_token');
        Route::get('/summary/traffic_user', [UserController::class, 'summaryTrafficUser'])->name('users.traffic_user');

    });

    // roles
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'list'])->name('roles.list');
        Route::post('/', [RoleController::class, 'create'])->name('roles.create');
        Route::get('/{id}', [RoleController::class, 'get'])->name('roles.get');
        Route::put('/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/{id}', [RoleController::class, 'delete'])->name('roles.delete');
    });

    // countries
    Route::prefix('countries')->group(function () {
        Route::get('/choose', [CountryController::class, 'getChoose'])->name('countries.choose');
        Route::get('/', [CountryController::class, 'list'])->name('countries.list');
        Route::post('/', [CountryController::class, 'create'])->name('countries.create');
        Route::put('/{country_id}', [CountryController::class, 'update'])->name('countries.update');
        Route::delete('/{country_id}', [CountryController::class, 'delete'])->name('countries.delete');

        // states
        Route::post('/{country_id}/states', [CountryStateController::class, 'create'])->name('states.create');
        Route::put('/{country_id}/states/{id}', [CountryStateController::class, 'update'])->name('states.update');
        Route::delete('/{country_id}/states/{id}', [CountryStateController::class, 'delete'])->name('states.delete');
    });

    // currencies
    Route::prefix('currencies')->group(function () {
        Route::get('/', [CurrencyController::class, 'list'])->name('currencies.list');
        Route::post('/', [CurrencyController::class, 'create'])->name('currencies.create');
        Route::get('/{id}', [CurrencyController::class, 'get'])->name('currencies.get');
        Route::put('/{id}', [CurrencyController::class, 'update'])->name('currencies.update');
        Route::delete('/{id}', [CurrencyController::class, 'delete'])->name('currencies.delete');
    });

    // Product
    Route::prefix('products')->group(function () {
        Route::get('/category', [ProductController::class, 'getCategories'])->name('category.list');
        Route::post('/category', [ProductController::class, 'storeCategory'])->name('category.create');
        Route::post('/category/{category}', [ProductController::class, 'updateCategory'])->name('category.update');
        Route::delete('/category/{category}', [ProductController::class, 'deleteCategory'])->name('category.delete');
        Route::post('/product-item', [ProductController::class, 'storeItem'])->name('product_item.create');
        Route::put('/product-item/{productitem}', [ProductController::class, 'updateItem'])->name('product_item.update');
        Route::get('/product-item', [ProductController::class, 'getItems'])->name('product_item.list');
        Route::delete('/product-item/{productitem}', [ProductController::class, 'deleteItem'])->name('product_item.delete');
        Route::post('/import/product-item', [ProductController::class, 'importItem'])->name('product_item.import');
    });
    Route::apiResource('/products', ProductController::class);

    // Email template
    Route::apiResource('/emailtemplates', EmailTemplateController::class);
 
    // Payment
    Route::apiResource('/payment', PaymentTransactionController::class);
    Route::prefix('payment')->group(function () {
        Route::post('/purchaseapprove/{paymentTransaction}', [PaymentTransactionController::class, 'purchaseApprove'])->name('payment.purchaseapprove');
        Route::post('/purchasecancel/{paymentTransaction}', [PaymentTransactionController::class, 'purchaseCancel'])->name('payment.purchasecancel');
        Route::get('/summary/total', [PaymentTransactionController::class, 'summaryTotal'])->name('payment.summary_total');
        Route::get('/summary/today', [PaymentTransactionController::class, 'summaryToday'])->name('payment.summary_today');
    });
    
    // Order
    Route::apiResource('/order', OrderController::class);
    Route::prefix('/order')->group(function () {
        Route::get('/product_item/list', [OrderController::class, 'listOrderProductItem'])->name('order.product_item.list');

    });
    // Article
    Route::prefix('/article')->group(function () {
        Route::get('/', [ArticleController::class, 'index'])->name('article.list');
        Route::post('/', [ArticleController::class, 'store'])->name('article.create');
        Route::post('/{article}', [ArticleController::class, 'update'])->name('article.update');
        Route::get('/{article}', [ArticleController::class, 'show'])->name('article.get');
        Route::delete('/{category}', [ArticleController::class, 'destroy'])->name('article.delete');
    });

    // Permissions
    Route::prefix('/permissions')->group(function () {
        // Route::get('/update_route_per', [PermissionsController::class, 'updateRouteNamePermission'])->name('permissions.update_route_per');
        Route::get('/route-name', [PermissionsController::class, 'getRouterName'])->name('permissions.router_name');

    });
    Route::apiResource('/permissions', PermissionsController::class);

    Route::prefix('file')->group(function () {
        Route::post('/', [FileController::class, 'store'])->name('file.create');

    });
});
