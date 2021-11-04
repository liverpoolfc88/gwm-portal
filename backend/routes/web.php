<?php

use Illuminate\Support\Facades\Route;
use App\User;
use Illuminate\Support\Facades\Hash;
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
    // dd(config());
    // $user = new User();
    // $user->username = 'admin';
    // $user->password = Hash::make('123456');
    // $user->save();
//    return User::first();

    return view('welcome');
});
