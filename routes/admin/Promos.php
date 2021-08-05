<?php
//Metrics / Key Performance Indicators...
//Show all promotions
Route::get('promotions/', 'PromotionsController@index')->name('promotions');

//Deal of The Day:
Route::get('promotions/dealOfTheDay/edit', 'PromotionsController@editDealOfTheDay')->name('promotion.dealOfTheDay');

Route::put('promotions/dealOfTheDay/update', 'PromotionsController@updateDealOfTheDay')->name('promotion.dealOfTheDay.update');

//Featured Products:
Route::get('promotions/featuredItems/edit', 'PromotionsController@editFeaturedItems')->name('featuredItems.edit');

Route::put('promotions/featuredItems/update', 'PromotionsController@updateFeaturedItems')->name('update.featuredItems');

//Featured Brands:
Route::get('promotions/featuredBrands/edit', 'PromotionsController@editFeaturedBrands')->name('featuredBrands.edit');

Route::put('promotions/featuredBrands/update', 'PromotionsController@updateFeaturedBrands')->name('update.featuredBrands');

//Featured Categories:
Route::get('promotions/featuredCategories/edit', 'PromotionsController@editFeaturedCategories')->name('promotion.featuredCategories.edit');

Route::put('promotions/featuredCategories/update', 'PromotionsController@updateFeaturedCategories')->name('promotion.featuredCategories.update');

//Trending Now Categories
Route::get('promotions/trendingNow/edit', 'PromotionsController@edittrendingNow')->name('promotion.trendingNow.edit');

Route::put('promotions/trendingNow/update', 'PromotionsController@updatetrendingNow')->name('promotion.trendingNow.update');

//Tagline
Route::get('promotions/tagline/edit', 'PromotionsController@editTagline')->name('promotion.tagline');

Route::put('promotions/tagline/update', 'PromotionsController@updateTagline')->name('promotion.tagline.update');

//Best Finds
Route::get('promotions/bestFinds/edit', 'PromotionsController@editBestFinds')->name('promotion.bestFindsUnder');

Route::put('promotions/bestFinds/update', 'PromotionsController@updateBestFinds')->name('promotion.bestFindsUnder.update');
