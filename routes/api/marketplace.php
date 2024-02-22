<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1/marketplace' ], function () {
    Route::post('products/process-order', 'ProductProcessOrderController');

    Route::get('products/categories', 'ProductController@categories');

    Route::get('products/{product}/similar', 'ProductController@similar');

    Route::apiResource('products', 'ProductController');
});
