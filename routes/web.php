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

Route::get('/', function () {
    return view('welcome');
});


//Cuando tengamos la autenticación de usuarios, podemos usar el middleware 'auth' para proteger las rutas de administrador. 'middleware' => 'auth'
Route::group(['prefix' => 'admin'], function () {

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/dashboard', function () {
        $data = [
            'title' => 'Dashboard de Administrador',
            'description' => 'Bienvenido al panel de administración',
        ];
        // Aquí podrías pasar datos a la vista si es necesario
        return view('admin.dashboard', $data);
    });
});
