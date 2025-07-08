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



Route::get('/publishings', 'PublishingController@index');
Route::post('/publishings', 'PublishingController@create');


//Ruta para el login
Route::post('/auth/login', 'AuthController@login');

//Rutas para crear usuarios (protegida)
//IMPORTANTE: require Bearer + Token en el header de la petición
Route::group(['middleware' => ['api'], 'prefix' => 'auth'], function () {
    Route::get('/renew', 'AuthController@renew');
    Route::post('/logout', 'AuthController@logout');
});

Route::group(['middleware' => ['api'], 'prefix' => 'users'], function () {
    Route::get('getUsers', 'UserController@index');
    Route::post('createUser', 'UserController@create');
});

//Creando un grupo para los roles
Route::group(['middleware' => ['api'], 'prefix' => 'rol'], function () {
    Route::get('/getRol', 'RolController@index');
    Route::post('/createRol', 'RolController@create');
    Route::put('/updateRol/{id}', 'RolController@update');
    Route::delete('/deleteRol/{id}', 'RolController@delete');
});