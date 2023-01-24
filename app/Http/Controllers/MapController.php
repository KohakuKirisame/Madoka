<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Star;
use App\Models\User;
use App\Models\Station;
use App\Models\Planet;
use App\Models\PlanetType;
use Illuminate\Http\Request;

class MapController extends Controller {
    public function getData(Request $request) {
        $uid=$request->session()->get("uid");
        if(User::where("uid",$uid)->exists()) {
            $privilege = User::where("uid", $uid)->first()->privilege;
            if (!is_null($privilege)) {
                $stars = Star::get()->toArray();
                $countries = Country::get()->toArray();
                $stations = Station::get()->toArray();
                $planets = Planet::get()->toArray();
                $planetTypes = PlanetType::get()->toArray();
                return view("map",["stars"=>$stars,"countrys"=>$countries,
                    "stations"=>$stations,"planets"=>$planets,
                    "planetTypes"=>$planetTypes,
                    "privilege"=>$privilege]);
            }
        }else {
            return redirect("https://kanade.nbmun.cn");
        }
    }

    public function mapPage(Request $request){
        $uid=$request->session()->get("uid");
        if(User::where("uid",$uid)->exists()) {
            $privilege = User::where("uid", $uid)->first()->privilege;
            if (!is_null($privilege)) {
                $user=UserController::GetInfo($uid);
                return view("mappage",["user"=>$user]);
            }
        }else {
            return redirect("https://kanade.nbmun.cn");
        }
    }

    public function changeOwner(Request $request){
        $uid = $request->session()->get('uid');
        if(User::where("uid",$uid)->exists()) {
            $privilege = User::where("uid", $uid)->first()->privilege;
            if ($privilege == 0 || $privilege == 1) {
                $id = $request->input("id");
                $newOwner = $request->input("owner");
                $lastOwner = Star::where(["id"=>$id])->first()->owner;
                if ($lastOwner != '') {
                    $lastCStars = json_decode(Country::where(["tag"=>$lastOwner])->first()->stars,true);
                    foreach ($lastCStars as $key => $value) {
                        if ($value == $id) {
                            unset($lastCStars[$key]);
                            array_values($lastCStars);
                            break;
                        }
                    }
                    $lastCStars = json_encode($lastCStars,JSON_UNESCAPED_UNICODE);
                    Country::where(["tag"=>$lastOwner])->update(["stars"=>$lastCStars]);
                }
                Star::where(["id"=>$id])->update(["owner"=>$newOwner,"controller"=>$newOwner]);
                if (!$newOwner == '') {
                    $c = Country::where(["tag" => $newOwner])->first();
                    $CStars = json_decode($c->stars, true);
                    $CStars[] = $id * 1;
                    $CStars = json_encode($CStars, JSON_UNESCAPED_UNICODE);
                    Country::where(["tag" => $newOwner])->update(["stars" => $CStars]);
                    echo $c->color;
                }else{
                    echo "#ffffff";
                }
                return;
            }
        }else {
            return redirect("https://kanade.nbmun.cn");
        }
    }
    public function newPlanet(Request $request) {
        $uid = $request->session()->get('uid');
        if(User::where("uid",$uid)->exists()) {
            $privilege = User::where("uid", $uid)->first()->privilege;
            if ($privilege == 0 || $privilege == 1) {
                $id = $request->input("id");
                $type = $request->input("type");
                $star = Star::where(["id"=>$id])->first();
                if ($star->havePlanet == 0) {
                    $newP = new Planet();
                    $newP->position=$id;
                    $newP->name = $star->name;
                    $newP->type=$type;
                    $newP->owner = '';
                    $newP->controller = '';
                    $newP->size = 20;
                    $newP->pops = "[]";
                    $newP->districts = "{}";
                    $newP->product = '{"market":{"energy":0,"minerals":0,"grain":0,"consume_goods":0,"alloys":0,"gases":0,"motes":0,"crystals":0},"country":{"energy":0,"minerals":0,"grain":0,"consume_goods":0,"alloys":0,"gases":0,"motes":0,"crystals":0}}';
                    $newP->save();
                }
                else {
                    Planet::where(["position"=>$id])->update(["type"=>$type]);
                }
                if ($type == '') {
                    Star::where(["id"=>$id])->update(["havePlanet"=>0]);
                }
                else {
                    Star::where(["id"=>$id])->update(["havePlanet"=>1]);
                }
            }
        }else {
            return redirect("https://kanade.nbmun.cn");
        }
    }

}
