<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthApi\UserAuthController;
use App\Http\Controllers\Admin\CompaniesController;
use App\Http\Controllers\Admin\CustomersController;
use App\Http\Controllers\Admin\RequestsChangeCompanyInfoController;

use App\Http\Controllers\Customer\TrackerController;

use App\Http\Controllers\Owner\CompaniesController as OwnerCompaniesController;
use App\Http\Controllers\Owner\CustomersController as OwnerCustomersController;


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
Route::get('/getMe', [UserAuthController::class,'getMe'])->middleware(['auth:api']);

Route::middleware(['auth:api','owner'])->group(function (){
    Route::post('/register-customer', [UserAuthController::class,'registerCustomer']);
    Route::resource('/company', OwnerCompaniesController::class)->only(['index','store']);
    Route::resource('/customers', OwnerCustomersController::class)->only(['index','show','update']);
});


Route::prefix('admin')->middleware(['auth:api','admin'])->group(function (){
    Route::resource('/companies', CompaniesController::class);
    Route::resource('/customers', CustomersController::class)->only(['show','update']);
    Route::resource('/change-info-company', RequestsChangeCompanyInfoController::class)->only(['show','update']);
});

Route::middleware(['auth:api','customer'])->group(function (){
    Route::resource('/tracker', TrackerController::class)->only(['store','update','index']);
});

