<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/publishings', 'PublishingController@index');
Route::post('/publishings', 'PublishingController@create');

//Creando un grupo
Route::group(['prefix' => 'users'], function () {
    Route::get('/getUsers', 'UserController@index');
    Route::post('/createUser', 'UserController@create');
    Route::put('/updateUser/{id}', 'UserController@update');
    Route::delete('/deleteUser/{id}', 'UserController@delete');
});

//Creando un grupo para los roles
Route::group(['prefix' => 'rol'], function () {
    Route::get('/getRol', 'RolController@index');
    Route::post('/createRol', 'RolController@create');
    Route::put('/updateRol/{id}', 'RolController@update');
    Route::delete('/deleteRol/{id}', 'RolController@delete');
});