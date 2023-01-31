<?php

namespace App\Http\Controllers;

use App\Models\Army;
use App\Models\Country;
use App\Models\Fleet;
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
            $user = User::where("uid", $uid)->first();
            $privilege = $user->privilege;
            $country = $user->country;
            if (!is_null($privilege)) {
                $stars = Star::get()->toArray();
                foreach ($stars as $key => $value) {
                    $stars[$key]['resource'] = json_decode($value['resource'], true);
                }
                $countries = Country::get()->toArray();
                $stations = Station::get()->toArray();
                $planets = Planet::get()->toArray();
                $planetTypes = PlanetType::get()->toArray();
                $allied = [];
                $war = [];
                if ($privilege <= 1) {
                    $fleets = Fleet::get()->toArray();
                    $armys = Army::get()->toArray();
                } elseif ($privilege >=2) {
                    $allied = json_decode(Country::where(["tag"=>$country])->first()->alliedWith);
                    $war = json_decode(Country::where(["tag"=>$country])->first()->atWarWith);
                    $fleets = Fleet::where(["owner"=>$country])->get()->toArray();
                    $armys = Army::where(["owner"=>$country])->get()->toArray();
                    foreach ($stars as $star) {
                        if ($star['owner'] == $country || $star['controller'] == $country || in_array($star['owner'],$allied) || in_array($star['controller'],$allied)) {
                            $fleets = array_merge($fleets,Fleet::where(["position"=>$star['id']])->get()->toArray());
                            $armys = array_merge($armys,Army::where(["position"=>$star['id']])->get()->toArray());
                        }
                    }
                }
                $j = 0;
                return view("map",["stars"=>$stars,"countrys"=>$countries,
                    "stations"=>$stations,"planets"=>$planets,
                    "planetTypes"=>$planetTypes,
                    "fleets"=>$fleets,
                    "armys"=>$armys,
                    "allied"=>$allied,
                    "war" => $war,
                    "selfCountry"=>$country,
                    "j"=>$j,
                    "privilege"=>$privilege]);
            }
        }else {
            return redirect("https://kanade.nbmun.cn");
        }
    }
    public function getDataForMove(Request $request) {
        $uid=$request->session()->get("uid");
        if(User::where("uid",$uid)->exists()) {
            $privilege = User::where("uid", $uid)->first()->privilege;
            if (!is_null($privilege)) {
                $stars = Star::get()->toArray();
                foreach ($stars as $key => $value) {
                    $stars[$key]['resource'] = json_decode($value['resource'], true);
                }
                $countries = Country::get()->toArray();
                $stations = Station::get()->toArray();
                $planets = Planet::get()->toArray();
                $planetTypes = PlanetType::get()->toArray();
                return view("mapformove",["stars"=>$stars,"countrys"=>$countries,
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
                if (key_exists("Err",$user)){
                    return redirect("/Action/Logout");
                }
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
                            array_splice($lastCStars,$key,1);
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
    public function setTradeHub(Request $request) {
        $uid = $request->session()->get('uid');
        $user= UserController::GetInfo($uid);
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        $id = $request->input('id');
        $star = Star::where(["id"=>$id])->first();
        $Country = Country::where(["tag"=>$country])->first();
        if ($star->owner == $country) {
            if ($star->isTradeHub == 1) {
                $star->isTradeHub = 0;
                $star->save();
            } else {
                $hubs = intval(1+count(json_decode($Country->planets))/3);
                $stars = Star::where(["owner"=>$country])->get()->toArray();
                $i = 0;
                foreach ($stars as $item){
                    if ($item['isTradeHub'] == 1) {
                        $i++;
                    }
                }
                if ($i < $hubs) {
                    $star->isTradeHub = 1;
                    $star->save();
                }
            }
        }
    }

}
