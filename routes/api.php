<?php

// Import routes for API V1

use App\Http\Controllers\Api\V1\Admin\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\User\ShipmentController;
use App\Http\Controllers\Api\V1\User\UserController as ApiV1UserController;
use App\Http\Controllers\Api\V1\Order\OrderController as ApiV1OrderController;

// ADMIN Routes
use App\Http\Controllers\Api\V1\User\AuthController as ApiV1UserAuthController;
use App\Http\Controllers\Api\V1\Admin\ShipmentController as AdminShipmentController;
use App\Http\Controllers\Api\V1\Product\ProductController as ApiV1ProductController;
use App\Http\Controllers\Api\V1\Product\CategoryController as ApiV1CategoryController;
use App\Http\Controllers\ControlPanel\ApiV1\AuthController as ControlPanelAuthController;
use App\Http\Controllers\Api\V1\Product\SubCategoryController as ApiV1SubCategoryController;
use App\Http\Controllers\ControlPanel\ApiV1\CouponController as ControlPanelCouponController;
use App\Http\Controllers\ControlPanel\ApiV1\User\UserController as ControlPanelUserController;
use App\Http\Controllers\ControlPanel\ApiV1\Order\OrderController as ControlPanelOrderController;
use App\Http\Controllers\ControlPanel\ApiV1\Products\ProductController as ControlPanelProductController;
use App\Http\Controllers\ControlPanel\ApiV1\Products\CategoryController as ControlPanelCategoryController;
use App\Http\Controllers\ControlPanel\ApiV1\Products\SubCategoryController as ControlPanelSubCategoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {

    Route::post('register', [ApiV1UserAuthController::class, 'register'])->name('api.user.register');
    Route::post('login', [ApiV1UserAuthController::class, 'login'])->name('api.user.login');
    Route::post('forgot-password', [ApiV1UserAuthController::class, 'sendPasswordLink'])->name('api.password.forgot');
    Route::post('reset-password', [ApiV1UserAuthController::class, 'resetPassword'])->middleware('auth:sanctum');

    Route::post('user/request-activation', [ApiV1UserAuthController::class, 'sendActivateOtp']);
    Route::post('user/activate', [ApiV1UserAuthController::class, 'activateAccount']);

    Route::middleware('auth:user')->group(callback: function () {
        // Email Routes
        Route::prefix('email')->group(function () {
            Route::post('otp', [ApiV1UserAuthController::class, 'sendEmailOtp'])->name('api.email.otp');
            Route::post('verify', [ApiV1UserAuthController::class, 'verifyEmail'])->name('api.email.verify');
        });

        // User Routes
        Route::group([], function () {
            Route::get('profile', [ApiV1UserController::class, 'getProfile'])->name('api.user.profile');
            Route::get('addresses', [ApiV1UserController::class, 'getAddresses'])->name('api.user.address');
            // ->middleware('requires_recaptcha');

            Route::prefix('shipment')->group(function () {
                Route::patch('cancel/{shipmentId}', [ShipmentController::class, 'cancelShipment']);
                Route::post('', [ShipmentController::class, 'create_shipment']);
                Route::post('confirm', [ShipmentController::class, 'confirmShipment']);
                // routes/web.php

                // Route to initiate update and generate summary
                Route::patch('{shipmentId}', [ShipmentController::class, 'update_shipment']);

                // Route to confirm and save the update
                Route::patch('confirm/{shipmentId}', [ShipmentController::class, 'confirmUpdateShipment']);

                Route::get('', [ShipmentController::class, 'shipments']);
                Route::get('zone', [ShipmentController::class, 'getShipmentZone']);
                Route::get('drop-off-points', [ShipmentController::class, 'dropOffPoints']);
            });



            Route::post('update-profile', [ApiV1UserController::class, 'updateProfile'])->name('api.user.profile.update');

            Route::prefix('admin')->group(function () {
                Route::prefix('user')->group(function () {
                    Route::post('', [UserController::class, 'createUser']);
                    Route::patch('{userId}', [UserController::class, 'updateUser']);
                    Route::get('', [UserController::class, 'listUsers']);
                    Route::delete('{userId}', [UserController::class, 'DeleteUser']);
                });

                Route::prefix('shipment')->group(function () {
                    Route::patch('approve/{shipmentId}', [AdminShipmentController::class, 'approveShipment']);
                    Route::patch('reject/{shipmentId}', [AdminShipmentController::class, 'rejectShipment']);

                    Route::prefix('zone')->group(function () {
                        Route::post('', [ShipmentController::class, 'createShipmentZone']);
                        // Route::get('', [ShipmentController::class, 'getShipmentZone']);
                    });
                });

                Route::prefix('config')->group(function () {
                    Route::post('', [AdminController::class, 'addConfig']);
                });
            });
        });

        // Product Routes

    });
    /**
     * For Routes that requires reCAPTCHA use this middleware "requires_recaptcha"
     * You can check the code in app/Http/Middleware/RecaptchaChecker.php
     * eg
     * Route::get("contact-us", "ContactController@contactUs")->middleware("requires_recaptcha");
     */
});



Route::prefix('admin')->group(function () {
    Route::post('login', [ControlPanelAuthController::class, 'login'])->name('api.user.login');

    // Email Routes
    Route::post('otp', [ControlPanelAuthController::class, 'sendEmailOtp'])->name('api.email.otp'); // Receive OTP for email verification
    Route::post('verify', [ControlPanelAuthController::class, 'verifyEmail'])->name('api.email.verify'); // Verify Email With OTP

    Route::middleware('auth:admin')->group(function () {
        // Product Routes
        Route::apiResource('product', ControlPanelProductController::class)->middleware(["requiresPermission:access_product"]);
        Route::apiResource('category', ControlPanelCategoryController::class)->middleware(["requiresPermission:access_category"]);
        Route::apiResource('subcategory', ControlPanelSubCategoryController::class)->middleware(["requiresPermission:access_subcategory"]);

        // Order Routes
        Route::prefix('order')->group(function () {
            Route::apiResource('', ControlPanelOrderController::class)->parameter('', 'orderId');
            Route::post('summary', [ControlPanelOrderController::class, 'summary']);
        });

        // User Credit Route
        Route::prefix('user')->group(function () {
            Route::post('{userId}/credit', [ControlPanelUserController::class, 'creditUser']);
        });
    });
});
