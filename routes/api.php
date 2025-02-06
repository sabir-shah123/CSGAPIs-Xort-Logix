<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthKeyController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//refresh the token
Route::get('/refresh-auth',[AuthKeyController::class,'refreshAuth']);


//Final Expense Routes
Route::prefix('final_expense')->group(function(){
    //Get the CSG Quotes
    Route::post('/get-quotes',[AuthKeyController::class,'getQuotes']);
    //get Open Companies
    Route::get('/get-open-companies',[AuthKeyController::class,'openCompanies']);
});

//Medicare Advantage Routes
Route::prefix('medicare_advantage')->group(function(){
    //Get the Quotes Collection
    Route::post('/get-quotes',[AuthKeyController::class,'getQuotesMA']);
    //get a single quote
    Route::post('/get-quote',[AuthKeyController::class,'getSingleQuoteMA']);
    //Market Penetration
    Route::post('/market-penetration',[AuthKeyController::class,'marketPenetrationMA']);
    //Market Contract Enrollment
    Route::post('/market-contract-enrollment',[AuthKeyController::class,'marketContractEnrollmentMA']);
    //Companies Collection
    Route::get('/medicare-advantage-companies',[AuthKeyController::class,'medicareAdvantageCompanies']);
   
});

