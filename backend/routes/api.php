<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/users/show','UserController@show');

    Route::post('/excel/upload', 'ExcelController@store');
    Route::get('/excel/clear', 'ExcelController@clear');
    Route::post('/excel/vin', 'ExcelController@producedvinIndex');
    Route::get('/producedtempvin', 'ExcelController@produced');
    Route::post('/producedvin', 'ExcelController@producedvin');
    Route::post('/excelvin/update', 'ExcelController@update');

    Route::post('/dealers','DealersController@index');
    Route::post('/dealers/update','DealersController@update');
    Route::delete('/dealers/delete/{id}', 'DealersController@destroy');

    Route::post('/realizevin','RealizedVinsController@index');
    Route::post('/realizevin/create','RealizedVinsController@store');
    Route::post('/realizevin/update','RealizedVinsController@update');
    Route::delete('/realizevin/delete/{id}', 'RealizedVinsController@destroy');

    Route::get('/role','UserController@role');
    Route::post('/users/update-role', 'UserController@updateRole');
    Route::post('/users','UserController@index');
    Route::post('/users/update','UserController@update');
    Route::post('/users/password','UserController@password');
    Route::delete('/users/delete/{id}', 'UserController@destroy');

    Route::get('soap/{params}', 'SoapController@index');
    Route::get('soapss6', 'SoapController@testss6');
    Route::get('soapss5', 'SoapController@testss5');
    Route::get('soapss4', 'SoapController@testss4');

    Route::post('/ii001files','II001FileController@index');
    Route::post('/ii002files','II002FileController@index');
    Route::get('/filecreate/{filetype}', 'II001FileController@filecreate');
    Route::get('/filesend/{filetype}', 'II001FileController@filesend');


    Route::post('/roles/update-role-permission', 'RoleController@updateRolePermission');
    Route::get('/roles', 'RoleController@index');
    Route::get('/roles/get-ref', 'RoleController@getRef');
    Route::get('/roles/show', 'RoleController@show');
    Route::delete('/roles/delete/{id}', 'RoleController@destroy');
    Route::post('/roles/update', 'RoleController@update');

    Route::post('permissions', 'PermissionController@index');
    Route::get('permissions/show', 'PermissionController@show');
    Route::delete('permissions/delete/{id}', 'PermissionController@destroy');
    Route::post('permissions/update', 'PermissionController@update');

});









