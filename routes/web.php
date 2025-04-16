<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingController;

Route::get('/', function () {
    return view('welcome');
});


/* GHL Auth connections */
Route::prefix('authorization')->name('crm.')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/crm/fetch_detail', [CRMController::class, 'crmFetchDetail'])->name('fetchDetail');
        Route::get('/crm/fetchLocations', [CRMController::class, 'fetchLocations'])->name('fetchLocations');
        Route::get('/crm/fetchUser', [CRMController::class, 'fetchUsers'])->name('fetchUser');
        Route::get('/crm/customfiled', [CRMController::class, 'fetchCustomField'])->name('customfiled');
    });
    Route::get('/crm/oauth/callback', [CRMController::class, 'crmCallback'])->name('oauth_callback');
});