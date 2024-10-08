<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/logs/{action?}', function ($action = null) {
    $logFilePath = storage_path('logs/laravel.log');

    // Check if the log file exists
    if (File::exists($logFilePath)) {
        if ($action === 'clear') {
            // Clear the log file
            File::put($logFilePath, '');
            return "Log file cleared.";
        }

        // Read the log file and return the content as plain text
        $logs = File::get($logFilePath);
        return Response::make(nl2br($logs), 200)
            ->header('Content-Type', 'text/html');
    }

    return "Log file does not exist.";
});

// Route::get('mail', function(){
//     return view('mails.email_verification');
// });

Route::get('db/update/demo', function () {
    DB::statement("ALTER TABLE `users` ADD COLUMN `mobile_number` VARCHAR(255)");
    DB::statement("ALTER TABLE `shipments` CHANGE `user_id` `user_id` bigint unsigned UNSIGNED DEFAULT NULL ;");
    DB::stament("ALTER TABLE `shipments` ADD COLUMN `user_name` VARCHAR(255);");
    DB::stament("ALTER TABLE `shipments` ADD COLUMN `email` VARCHAR(255);");
    DB::stament("ALTER TABLE `shipments` ADD COLUMN `mobile_number` VARCHAR(255);");
});
