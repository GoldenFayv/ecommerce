<?php

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
