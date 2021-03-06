<?php
Route::get('dispute/{dispute}/response', 'DisputeController@response')->name('dispute.response');

Route::post('dispute/{dispute}/response', 'DisputeController@storeResponse')->name('dispute.storeResponse');

Route::resource('dispute', 'DisputeController')->only(['index', 'show']);
