<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Star;
use App\Models\User;
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
                Planet::where(["position"=>$id])->update(["type"=>$type]);
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
