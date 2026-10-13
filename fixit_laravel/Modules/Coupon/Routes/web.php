<?php

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

Route::group(['middleware' => ['auth'], 'namespace' => 'Backend', 'as' => 'backend.'], function () {

    // Coupon
    Route::resource('backend/coupon', 'CouponController', ['except' => ['show']]);
    // These three were not covered by authorizeResource() — any signed-in user could change coupons
    Route::post('backend/coupon-status', 'CouponController@toggleStatus')->name('coupon-status')->middleware('can:backend.coupon.edit');
    Route::put('coupon/status/{id}', 'CouponController@status')->name('coupon.status')->middleware('can:backend.coupon.edit');
    Route::delete('delete-coupons', 'CouponController@deleteRows')->name('delete.coupons')->middleware('can:backend.coupon.destroy');

});
