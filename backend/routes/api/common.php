<?php

use Illuminate\Support\Facades\Route;
// controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentTransactionController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\UserController;

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/email/check', [AuthController::class, 'checkEmailExisted']);

Route::get('/auth/facebook', [AuthController::class, 'redirectToFacebook']);
Route::get('/auth/facebook/callback', [AuthController::class, 'loginWithFacebook']);
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'loginWithGoogle']);

Route::get('/permissions/refresh', [CommonController::class, 'generatePermissions']);
Route::get('/config', [CommonController::class, 'getConfig']);
Route::get('/products/tree-category', [ProductController::class, 'getTreeCategory']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'clientProductDetail']);
Route::get('/transaction/latest', [PaymentTransactionController::class, 'getTransactionHistory']);
Route::get('/setting/homepage', [CommonController::class, 'getHomePage']);

Route::group(['middleware' => ['jwt.auth', 'verify.status'], 'as' => 'common.'], function () {
    Route::get('/auth', [AuthController::class, 'getAuthUser']);
    Route::post('/auth', [AuthController::class, 'updateAuthUser']);

    Route::post('/send-otp/email', [AuthController::class, 'sendOtpViaEmail']);
    Route::post('/send-otp/mobile', [AuthController::class, 'sendOtpViaMobile']);
    Route::post('/verify', [AuthController::class, 'verifyOtp']);

    Route::prefix('payment')->group(function () {
        Route::post('/qrcode', [PaymentTransactionController::class, 'getQrCode']);
        Route::post('/purchase', [PaymentTransactionController::class, 'createTransaction']);
    });

    //  Order
    Route::get('/order', [OrderController::class, 'getClientOrders']);
    Route::get('/order/{order}', [OrderController::class, 'getClientOrderDetail']);
    // Transactions
    
    Route::get('/transaction', [PaymentTransactionController::class, 'getClientTransactions']);
    Route::get('/transaction/{transaction}', [PaymentTransactionController::class, 'getClientTransactionDetail']);

    // User
    Route::put('/users/update', [UserController::class, 'updateClient']);

    // Check live UID facebook
    Route::post('/tool/check-live-uid', [ToolController::class, 'checkLiveUidFacebook']);
    
    // permisisons
    Route::get('/permissions', [CommonController::class, 'getPermissions']);
    Route::post('/client-buy', [OrderController::class, 'clientBuy']);

});

Route::post('/debug/deploy', [DebugController::class, 'deploy']);
Route::get('/debug/info', [DebugController::class, 'info']); 
Route::get('/debug/log', [DebugController::class, 'getLog']); 
Route::get('/', function() {
    return 'Lấy đường dẫn URL trang web';
})->name('home'); 
