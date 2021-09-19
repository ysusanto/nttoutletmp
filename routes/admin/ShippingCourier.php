<?php
Route::delete('shippingCourier/delete/{shippingCourier}', 'ShippingCourierController@remove')->name('shippingCourier.trash');

// Route::get('shippingCourier/{shippingCourier}', 'ShippingCourierController@edit')->name('shippingCourier.edit');
Route::get('shippingCourier/create', 'ShippingCourierController@create')->name('shippingCourier.create');
Route::get('shippingCourier/subsdistrict', 'ShippingCourierController@getsubdistrict')->name('shippingCourier.subsdistrict');
Route::get('shippingCourier/{shippingCourier}', 'ShippingCourierController@edit')->name('shippingCourier.edit');
Route::post('shippingCourier/save', 'ShippingCourierController@store')->name('shippingCourier.save');

Route::resource('shippingCourier', 'ShippingCourierController');
