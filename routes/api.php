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

//Creando grupo para las publicaciones
Route::group(['middleware' => ['api'], 'prefix' => 'publishings'], function () {
    Route::get('getPublishings', 'PublishingController@index');
    Route::get('getPublishing/{id?}', 'PublishingController@find');
    Route::post('createPublishing', 'PublishingController@create');
    Route::post('updatePublishing/{id?}', 'PublishingController@update');
    Route::post('updateStatus/{id?}', 'PublishingController@toggleStatus');
    Route::post('deletePublishing/{id?}', 'PublishingController@delete');
    Route::get('summary', 'PublishingController@summary');
});

//Creando un grupo para los roles
Route::group(['middleware' => ['api'], 'prefix' => 'rol'], function () {
    Route::get('getRol', 'RolController@index');
    Route::post('createRol', 'RolController@create');
    // Route::put('updateRol/{id}', 'RolController@update');
    // Route::delete('deleteRol/{id}', 'RolController@delete');
});

Route::group(['middleware' => ['api'], 'prefix' => 'authors'], function () {
    Route::get('getAuthors', 'AuthorController@index');
    Route::get('getAuthor/{id}', 'AuthorController@find');
    Route::post('createAuthor', 'AuthorController@create');
    Route::post('updateAuthor', 'AuthorController@update');
    Route::delete('deleteAuthor/{id}', 'AuthorController@delete');
});

// Route::group(['middleware' => ['api'], 'prefix' => 'privileges'], function () {
//     Route::get('getPrivileges', 'UserPrivilegeController@index');
//     Route::get('getPrivilege/{id?}', 'UserPrivilegeController@find');
//     Route::post('createPrivilege', 'UserPrivilegeController@create');
//     Route::post('updatePrivilege', 'UserPrivilegeController@update');
//     Route::delete('deletePrivilege/{id}', 'UserPrivilegeController@delete');
// });

Route::group(['middleware' => ['api'], 'prefix' => 'categories'], function () {
    Route::get('getCategories', 'CategoryController@index');
    Route::get('getCategory/{id?}', 'CategoryController@find');
    Route::post('createCategory', 'CategoryController@create');
    Route::post('updateCategory/{id?}', 'CategoryController@update');
    Route::post('toggleState/{id?}', 'CategoryController@toggleState');
});

Route::group(['middleware' => ['api'], 'prefix' => 'publishingDetails'], function () {
    Route::post('createPublishingDetail', 'PublishingDetailController@create');
    Route::post('updatePublishingDetail/{id?}', 'PublishingDetailController@update');
    Route::post('deletePublishingDetail/{id?}', 'PublishingDetailController@delete');
});