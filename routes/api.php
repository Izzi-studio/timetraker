<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthApi\UserAuthController;
use App\Http\Controllers\Admin\CompaniesController;
use App\Http\Controllers\Admin\CustomersController;
use App\Http\Controllers\Admin\RequestsChangeCompanyInfoController;
use App\Http\Controllers\Admin\TrackerController;

use App\Http\Controllers\Customer\TrackerController as CustomerTrackerController;

use App\Http\Controllers\Owner\CompaniesController as OwnerCompaniesController;
use App\Http\Controllers\Owner\CustomersController as OwnerCustomersController;
use App\Http\Controllers\Owner\TrackerController as OwnerTrackerController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [UserAuthController::class,'registerCompany']);
Route::post('/login', [UserAuthController::class,'login']);
Route::post('/logout', [UserAuthController::class,'logout'])->middleware(['auth:api']);
Route::get('/getMe', [UserAuthController::class,'getMe'])->middleware(['auth:api']);

Route::prefix('owner')->middleware(['auth:api','owner'])->group(function (){
    Route::post('/register-customer', [UserAuthController::class,'registerCustomer']);
    Route::resource('/company', OwnerCompaniesController::class)->only(['index','store']);
    Route::resource('/customers', OwnerCustomersController::class)->only(['index','show','update']);
    Route::get('/statistic/{customer}', [OwnerTrackerController::class,'tableStatistic']);
    Route::get('/statistic/tracker/{tracker}', [OwnerTrackerController::class,'show']);
    Route::put('/statistic/tracker/{tracker}', [OwnerTrackerController::class,'update']);
});


Route::prefix('customer')->middleware(['auth:api','customer'])->group(function (){
    Route::get('/statistic', [CustomerTrackerController::class,'tableStatistic']);
    Route::resource('/tracker', CustomerTrackerController::class)->only(['store','update','index','show']);
});

Route::prefix('admin')->middleware(['auth:api','admin'])->group(function (){
    Route::resource('/companies', CompaniesController::class)->only(['index','show','update']);
    Route::resource('/customers', CustomersController::class)->only(['index','show','update']);
    Route::resource('/change-info-company', RequestsChangeCompanyInfoController::class)->only(['show','update']);

    Route::get('/statistic/{customer}', [TrackerController::class,'tableStatistic']);
    Route::get('/statistic/tracker/{tracker}', [TrackerController::class,'show']);
    Route::put('/statistic/tracker/{tracker}', [TrackerController::class,'update']);
});
