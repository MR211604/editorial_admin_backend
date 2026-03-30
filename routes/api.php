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
    Route::post('updateUser/{id?}', 'UserController@update');
    Route::post('toggleActive/{id?}', 'UserController@toggleActive');
});

//Creando grupo para las publicaciones
Route::group(['middleware' => ['api'], 'prefix' => 'publishings'], function () {
    Route::get('getPublishings', 'PublishingController@index');
    Route::get('getPublishing/{id?}', 'PublishingController@find');
    Route::get('searchPublishing/{publishingURL?}', 'PublishingController@searchByURL');
    Route::post('createPublishing', 'PublishingController@create');
    Route::post('updatePublishing/{id?}', 'PublishingController@update');
    Route::post('updateStatus/{id?}', 'PublishingController@toggleStatus');
    Route::post('deletePublishing/{id?}', 'PublishingController@delete');
    Route::get('summary', 'PublishingController@summary');
    Route::get('globalSearch', 'PublishingController@globalSearch');
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
    Route::get('getAuthor/{id?}', 'AuthorController@find');
    Route::post('createAuthor', 'AuthorController@create');
    Route::post('updateAuthor/{id?}', 'AuthorController@update');
    Route::post('deleteAuthor/{id?}', 'AuthorController@delete');
});

Route::group(['middleware' => ['api'], 'prefix' => 'categories'], function () {
    Route::get('getCategories', 'CategoryController@index');
    Route::get('getCategory/{id?}', 'CategoryController@find');
    Route::get('searchCategory/{categoryURL?}', 'CategoryController@searchByURL');
    Route::post('createCategory', 'CategoryController@create');
    Route::post('updateCategory/{id?}', 'CategoryController@update');
    Route::post('toggleState/{id?}', 'CategoryController@toggleState');
    Route::post('uploadCategoryImage', 'CategoryController@storeCategoryImage');
});

Route::group(['middleware' => ['api'], 'prefix' => 'publishingDetails'], function () {
    Route::post('createPublishingDetail', 'PublishingDetailController@create');
    Route::post('updatePublishingDetail/{id?}', 'PublishingDetailController@update');
    Route::post('deletePublishingDetail/{id?}', 'PublishingDetailController@delete');
});

Route::group(['middleware' => ['api'], 'prefix' => 'categoryTypes'], function () {
    Route::get('getCategoryTypes', 'CategoryTypeController@index');
});

Route::group(['middleware' => ['api'], 'prefix' => 'banner'], function () {
    Route::get('index', 'BannerController@index');
    Route::get('getLastActiveBanner', 'BannerController@getLastActiveBanner');
    Route::post('createBanner', 'BannerController@create');
    Route::post('updateBanner/{id?}', 'BannerController@update');
    Route::post('toggleState/{id?}', 'BannerController@toggleState');
});