<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Use App\Models\User;
Use App\Models\Article;
Use App\Http\Controllers\ArticleController;
Use App\Http\Controllers\brandsData;
Use App\Http\Controllers\itemsfile;
Use App\Http\Controllers\customersData;
Use App\Http\Controllers\dashboard;
Use App\Http\Controllers\invoiceController;
Use App\Http\Controllers\authentication;


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});




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

// Route::get('login', [authentication::class, 'login']);

Route::middleware(['setSupportdb'])->group(function () {
    Route::post('/login', [authentication::class, 'login']);
});
