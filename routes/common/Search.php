<?php

Route::group(
  ['middleware' => ['ajax']],
  function () {
    Route::get('search/customer', 'SearchController@findCustomer')->name('search.customer');

    Route::get('search/product', 'SearchController@findProduct')->name('search.product');

    Route::get('message/search', 'SearchController@findMessage')->name('message.search');

    Route::get('search/merchant', 'SearchController@findMerchant')->name('search.merchant');

    Route::get('search/findProduct', 'SearchController@findProductForSelect')->name('search.findProduct');

    Route::get('search/findInventory', 'SearchController@findInventoryForSelect')->name('search.findInventory');
  }
);
