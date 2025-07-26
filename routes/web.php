<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\itemsfile;
use App\Http\Controllers\brandsData;
use App\Http\Controllers\customersData;
use App\Http\Controllers\dashboard;
use App\Http\Controllers\invoiceController;
use App\Http\Controllers\authentication;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api')->group(function () {
    Route::get('brands', [brandsData::class, 'index']);

    Route::get('items', [itemsfile::class, 'index']);
    Route::get('items/minmax', [itemsfile::class, 'minMaxItem']);

    Route::get('customers', [customersData::class, 'index']);
    Route::get('customers/topCustomers', [customersData::class, 'TopCustomers']);

    Route::get('accountstatments', [customersData::class, 'accountstatments']);

    Route::get('dashboard', [dashboard::class, 'index']);
    Route::get('dashboard/chart', [dashboard::class, 'getMonthlyNetAmountForCurrentYear']);

    Route::get('invoices', [invoiceController::class, 'index']);
    Route::get('invoices/sumData', [invoiceController::class, 'summData']);
    Route::get('invoices/invoiceDetails', [invoiceController::class, 'invoiceDetails']);

    Route::middleware(['setSupportdb'])->group(function () {
        Route::post('login', [authentication::class, 'login']);
    });
});