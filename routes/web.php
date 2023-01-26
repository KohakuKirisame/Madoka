<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\PlanetController;

use Illuminate\Support\Facades\Session;

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
    return redirect("/Dashboard");
});

Route::get('/Dashboard',[UserController::class,"ShowDashboard"])->middleware("isAuthedByReimu");

Route::get('/Login',function () {
    if (Session::exists("uid")&&Session::get("valid")>time()) {
        return redirect("/Dashboard");
    } else {
        Session::flush();
        return view("login");
    }
});

Route::get('/Map',[MapController::class,"mapPage"])->middleware("isAuthedByReimu");
Route::get("/MapContent",[MapController::class,"getData"])->middleware("isAuthedByReimu");

Route::get("/Planets",[PlanetController::class,"planetPage"])->middleware("isAuthedByReimu");

Route::prefix("Action")->group(function (){
    Route::get("/Login",[UserController::class,"Auth"]);
    Route::get("/Logout",[UserController::class,"LogOut"]);
    Route::post("/ChangeOwner",[MapController::class,"changeOwner"])->middleware("isAuthedByReimu");
    Route::post("/NewPlanet",[MapController::class,"newPlanet"])->middleware("isAuthedByReimu");
    Route::post("/AdminNewPop",[PlanetController::class,"adminNewPop"])->middleware("isAuthedByReimu");
    Route::post("/ReadPlanet",[PlanetController::class,"readPlanet"])->middleware("isAuthedByReimu");
    Route::post("/NewName",[PlanetController::class,"newName"])->middleware("isAuthedByReimu");
    Route::post("/ChangeSize",[PlanetController::class,"changeSize"])->middleware("isAuthedByReimu");
    Route::post("/BuildDistrict",[PlanetController::class,"buildDistrict"])->middleware("isAuthedByReimu");
});


