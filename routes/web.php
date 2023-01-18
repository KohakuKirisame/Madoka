<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;

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

Route::get('/Dashboard', function () {
    return view('dashboard');
})->middleware("isAuthedByReimu");

Route::prefix("Action")->group(function (){
    Route::get("/Login",[UserController::class,"Auth"]);
    Route::get("/Logout",[UserController::class,"LogOut"]);
});
