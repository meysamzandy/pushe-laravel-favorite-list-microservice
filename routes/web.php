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

Route::prefix('_api/_v1/watch')->group(static function () {
    Route::get('/','WatchListController@getList');
    Route::get('/{nid}','WatchListController@check');
    Route::post('/add/{nid}', 'WatchListController@addWatch');
    Route::delete('/remove/{nid}', 'WatchListController@removeWatch');
});
