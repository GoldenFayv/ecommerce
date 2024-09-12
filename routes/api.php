<?php

// Import routes for API V1
use App\Http\Controllers\Api\V1\User\UserController as ApiV1UserController;
use App\Http\Controllers\Api\V1\Order\OrderController as ApiV1OrderController;
use App\Http\Controllers\Api\V1\User\AuthController as ApiV1UserAuthController;
use App\Http\Controllers\Api\V1\Product\ProductController as ApiV1ProductController;
use App\Http\Controllers\Api\V1\Product\CategoryController as ApiV1CategoryController;

// ADMIN Routes
use App\Http\Controllers\ControlPanel\ApiV1\CouponController as ControlPanelCouponController;
use App\Http\Controllers\ControlPanel\ApiV1\AuthController as ControlPanelAuthController;
use App\Http\Controllers\Api\V1\Product\SubCategoryController as ApiV1SubCategoryController;
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

    Route::middleware('auth:user')->group(function () {
        // Email Routes
        Route::prefix('email')->group(function () {
            Route::post('otp', [ApiV1UserAuthController::class, 'sendEmailOtp'])->name('api.email.otp');
            Route::post('verify', [ApiV1UserAuthController::class, 'verifyEmail'])->name('api.email.verify');
        });

        // User Routes
        Route::prefix('user')->group(function () {
            Route::get('profile', [ApiV1UserController::class, 'getProfile'])
                ->name('api.user.profile')
                ->middleware('requires_recaptcha');
            Route::post('update-profile', [ApiV1UserController::class, 'updateProfile'])
                ->name('api.user.profile.update');
            Route::post('deactivate', [ApiV1UserAuthController::class, 'deactivateAccount'])
                ->name('api.user.deactivate');
            Route::post('request-delete', [ApiV1UserAuthController::class, 'requestDelete'])
                ->name('api.user.request.delete');
            Route::post('delete', [ApiV1UserAuthController::class, 'deleteAccount'])
                ->name('api.user.delete');
        });

        // Product Routes
        Route::apiResource('product', ApiV1ProductController::class);
        Route::apiResource('category', ApiV1CategoryController::class);
        Route::apiResource('subcategory', ApiV1SubCategoryController::class);

        // Order Routes
        Route::prefix('order')->group(function () {
            Route::apiResource('', ApiV1OrderController::class)->parameter('', 'orderId');
            Route::post('summary', [ApiV1OrderController::class, 'summary']);
        });
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
