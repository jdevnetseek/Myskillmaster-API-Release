<?php

use App\Enums\Role;
use App\Http\Controllers\V1\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'v1/admin',
], function () {

    Route::post('login', 'AuthController@login')->name('admin.auth.login');

    Route::get('notifications', 'NotificationController@index');

    Route::group(['middleware' => ['auth', 'user.role:' . Role::ADMIN . '|' . Role::SUPER_ADMIN]], function () {
        // Report
        Route::get('reports', 'Report\ReportController@index')
            ->name('admin.reports.index');
        
        Route::get('reports/categories', 'Report\ReportController@getReportCategories')
            ->name('admin.reports.categories');

        Route::get('reports/{report}', 'Report\ReportController@show')
            ->name('admin.reports.show');

        Route::put('reports/{report}', 'Report\ReportController@update')
            ->name('admin.reports.update');

        Route::get('report/users', 'Report\UserController')
            ->name('admin.report.users.index');
    });

    // User Access
    Route::group(['namespace' => 'User'], function () {
        Route::post('users/{user}/blocked', 'AccountSettingsController@blocked');

        Route::post('users/{user}/unblocked', 'AccountSettingsController@unblocked');
    });

    Route::name('admin.')->group(function () {
        // Categories
        Route::apiResource('categories', CategoryController::class);
    });

    // Settings
    Route::group(['prefix' => 'settings', 'namespace' => 'Settings'], function () {
        Route::get('digital-distribution', 'DigitalDistributionController@index');
        Route::post('digital-distribution', 'DigitalDistributionController@store');
    });

    // App Version Controller
    Route::group(['prefix' => 'app'], function () {
        Route::apiResource('version-control', 'AppVersionController');
    });

    // Settings
    Route::group(['prefix' => 'settings', 'namespace' => 'Settings'], function () {
        Route::post('pages', 'PageSettingsController@store');
        Route::get('pages/{pageType}', 'PageSettingsController@show');
        Route::get('pages', 'PageSettingsController@index');
    });
});
