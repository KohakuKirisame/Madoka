<?php

use App\Http\Controllers\CountryController;
use App\Http\Controllers\EconomyController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\PlanetController;
use App\Http\Controllers\MilitaryController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\MarketController;

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
Route::get("/MapForMoveContent",[MapController::class,"getDataForMove"])->middleware("isAuthedByReimu");

Route::get("/Planets",[PlanetController::class,"planetPage"])->middleware("isAuthedByReimu");
Route::get("/Military",[MilitaryController::class,"militaryPage"])->middleware("isAuthedByReimu");

Route::get("/News",[NewsController::class,"newsPage"])->middleware("isAuthedByReimu");
Route::get("/News/{id}",[NewsController::class,"newsDetail"])->middleware("isAuthedByReimu")->where("id","[0-9]+");
Route::get("/News/New",[NewsController::class,"newsNew"])->middleware("isAuthedByReimu");
Route::get("/News/Edit/{id}",[NewsController::class,"newsEdit"])->middleware("isAuthedByReimu")->where("id","[0-9]+");

Route::get("/Technology",[CountryController::class,"technologyPage"])->middleware("isAuthedByReimu");

Route::get("/Market",[MarketController::class,"marketPage"])->middleware("isAuthedByReimu");

Route::prefix("Action")->group(function () {
    Route::get("/Login", [UserController::class, "Auth"]);
    Route::get("/Logout", [UserController::class, "LogOut"]);
    Route::post("/MainFunction", [EconomyController::class, "allMainFunction"])->middleware("isAuthedByReimu");
    Route::post("/ChangeOwner", [MapController::class, "changeOwner"])->middleware("isAuthedByReimu");
    Route::post("/NewPlanet", [MapController::class, "newPlanet"])->middleware("isAuthedByReimu");
    Route::post("/SetTradeHub", [MapController::class, "setTradeHub"])->middleware("isAuthedByReimu");
    Route::post("/Colonize", [PlanetController::class, "colonize"])->middleware("isAuthedByReimu");
    Route::post("/AdminNewPop", [PlanetController::class, "adminNewPop"])->middleware("isAuthedByReimu");
    Route::post("/ReadPlanet", [PlanetController::class, "readPlanet"])->middleware("isAuthedByReimu");
    Route::post("/ChangePlanetName", [PlanetController::class, "changePlanetName"])->middleware("isAuthedByReimu");
    Route::post("/ChangeSize", [PlanetController::class, "changeSize"])->middleware("isAuthedByReimu");
    Route::post("/BuildDistrict", [PlanetController::class, "buildDistrict"])->middleware("isAuthedByReimu");
    Route::post("/BuildMarketDistrict", [PlanetController::class, "buildMarketDistrict"])->middleware("isAuthedByReimu");
    Route::post("/BuildArmy", [PlanetController::class, "buildArmy"])->middleware("isAuthedByReimu");
    Route::post("/AdminDeletePop", [PlanetController::class, "adminDeletePop"])->middleware("isAuthedByReimu");
    Route::post("/PlanetCount", [PlanetController::class, "planetCount"])->middleware("isAuthedByReimu");
    Route::post("/ReadFleet", [MilitaryController::class, "readFleet"])->middleware("isAuthedByReimu");
    Route::post("/ChangeFleetName", [MilitaryController::class, "changeFleetName"])->middleware("isAuthedByReimu");
    Route::post("/ChangeShipName", [MilitaryController::class, "changeShipName"])->middleware("isAuthedByReimu");
    Route::post("/ChangeFleetWeaponA", [MilitaryController::class,"changeFleetWeaponA"])->middleware("isAuthedByReimu");
    Route::post("/ChangeFleetWeaponB", [MilitaryController::class,"changeFleetWeaponB"])->middleware("isAuthedByReimu");
    Route::post("/ChangeFleetComputer", [MilitaryController::class, "changeFleetComputer"])->middleware("isAuthedByReimu");
    Route::post("/ChangeFleetFTL", [MilitaryController::class, "changeFleetFTL"])->middleware("isAuthedByReimu");
    Route::post("/NewShip", [MilitaryController::class, "newShip"])->middleware("isAuthedByReimu");
    Route::post("/GetFleets", [MilitaryController::class, "getFleets"])->middleware("isAuthedByReimu");
    Route::post("/FleetMerge", [MilitaryController::class, "fleetMerge"])->middleware("isAuthedByReimu");
    Route::post("/ShipTrans", [MilitaryController::class, "shipTrans"])->middleware("isAuthedByReimu");
    Route::post("/FleetDelete", [MilitaryController::class, "fleetDelete"])->middleware("isAuthedByReimu");
    Route::post("/ChangeArmyName", [MilitaryController::class, "changeArmyName"])->middleware("isAuthedByReimu");
    Route::post("/ArmyDelete", [MilitaryController::class, "deleteArmy"])->middleware("isAuthedByReimu");
    Route::post("/NewFleet", [MilitaryController::class, "newFleet"])->middleware("isAuthedByReimu");
    Route::post("/Move", [MilitaryController::class, "move"])->middleware("isAuthedByReimu");
    Route::post("/SaveNews",[NewsController::class,"newsSave"])->middleware("isAuthedByReimu");
    Route::get("/PassNews/{id}",[NewsController::class,"newsPass"])->middleware("isAuthedByReimu");
    Route::post("/ChangeTax",[CountryController::class,"changeTax"])->middleware("isAuthedByReimu");
    Route::post("/ChooseTech",[CountryController::class,"chooseTech"])->middleware("isAuthedByReimu");
    Route::post("/DeleteTech",[CountryController::class,"deleteTech"])->middleware("isAuthedByReimu");
    Route::post("/ChangeAllowance",[CountryController::class,"setTechAllowance"])->middleware("isAuthedByReimu");
    Route::post("/AdminAddTech",[CountryController::class,"adminAddTech"])->middleware("isAuthedByReimu");
    Route::post("/ReadMarket",[MarketController::class,"readMarket"])->middleware("isAuthedByReimu");
    Route::post("/NewTrade",[MarketController::class,"newTrade"])->middleware("isAuthedByReimu");
    Route::post("/DeleteTrade",[MarketController::class,"deleteTrade"])->middleware("isAuthedByReimu");

    Route::post("/UpdateRes",[PlanetController::class,"updateRes"])->middleware("isAuthedByReimu");
    Route::post("/Battle",[MilitaryController::class,"Battle"])->middleware("isAuthedByReimu");
});


