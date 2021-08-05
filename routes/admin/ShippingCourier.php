<?php
// Route::delete('shippingCourier/{shippingCourier}', 'ShippingZoneController@remove')->name('shippingCourier.remove');

// Route::get('shippingCourier/{shippingCourier}', 'ShippingCourierController@edit')->name('shippingCourier.edit');
// Route::get('shippingCourier/create', 'ShippingCourierController@create')->name('shippingCourier.create');
Route::get('shippingCourier/subsdistrict', 'ShippingCourierController@getsubdistrict')->name('shippingCourier.subsdistrict');
// Route::get('shippingCourier/coba', 'ShippingCourierController@coba')->name\('coba');
// Route::post('shippingCourier/save', 'ShippingCourierController@saveStore')->name('shippingCourier.save');

Route::resource('shippingCourier', 'ShippingCourierController');
