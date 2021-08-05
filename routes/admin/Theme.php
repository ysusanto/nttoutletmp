<?php
// Theme
Route::get('/theme', 'ThemeController@all')->name('theme.index');

Route::put('/theme/activate/{theme}/{type?}', 'ThemeController@activate')->name('theme.activate');
