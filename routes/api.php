<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('login', 'API\AuthController@login');
Route::post('register', 'API\AuthController@register');

Route::group(['middleware' => 'login.check'], function () {
    Route::resource('items', 'API\ItemsController');
    Route::resource('invoices', 'API\InvoicesController');

    Route::post('invoice/next-id', 'API\InvoicesController@getNextInvoiceId');
    Route::get('invoice/validate-id/{invoice_id}', 'API\InvoicesController@validateInvoiceId');

    Route::resource('customers', 'API\CustomersController');

});
