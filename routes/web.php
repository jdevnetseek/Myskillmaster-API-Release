<?php

use Stripe\AccountLink;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\Route;

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

Route::view('/', 'welcome')
    ->name('homepage');

Route::post('/stripe/webhook', 'V1\\Stripe\\WebhookController@handleWebhook');
Route::post('/stripe/connect/webhook', 'V1\\Stripe\\ConnectWebhookController@handleWebhook');
