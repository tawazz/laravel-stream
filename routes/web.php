<?php

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


Auth::routes();

Route::group([], function(){

    Route::get('/', 'VideoController@index');
    Route::post('/videos/delete/{video}', 'VideoController@delete');
    Route::get('/stream/{video}', 'VideoController@stream');

    Route::get('/uploader', 'VideoController@uploader')->name('uploader');

    Route::post('/upload', 'VideoController@store')->name('upload');
});
