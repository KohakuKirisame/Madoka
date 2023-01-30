<?php

namespace App\Http\Controllers;

use App\Models\Army;
use App\Models\Country;
use App\Models\Definition;
use App\Models\Fleet;
use App\Models\Planet;
use App\Models\Population;
use App\Models\Ship;
use App\Models\ShipType;
use App\Models\Star;
use App\Models\Tech;
use App\Models\TechArea;
use App\Models\User;
use Illuminate\Http\Request;
use Psy\Util\Json;

class CountryController
{
    var $tag;
//    var $id,$name,$color,$capital,$energy,$stars,$planets,$financeList,$policyList;
//    var $techs,$techList,$ModifierList;

    function __construct($tag=-1) {
        if($tag != -1) {
            $this->tag = $tag;
        }
    }
    public function chooseTech(Request $request) {
        $id = $request->input('id');
        $choose = $request->input('area');
        $categorys = json_decode(TechArea::where(["area"=>$choose])->first()->category,true);
        $category = $categorys[array_rand($categorys,1)];
        $nextTechs = [];
        $techFin = json_decode(Country::where(["tag"=>$id])->first()->techs,true);
        $techs = Tech::where(["category"=>$category])->get()->toArray();
        $techList = json_decode(Country::where(["tag"=>$id])->first()->techList,true);
        foreach ($techs as $tech) {
            if (in_array($tech['preTech'],$techFin)&&!in_array($tech['name'],$techFin)){
                $nextTechs[] = [$tech['name'],$tech['cost']];
                break;
            }
        }
        $nextTech = $nextTechs[array_rand($nextTechs,1)];
        $techList[] = ["area"=>$choose,"category"=>$category,"tech"=>"$nextTech[0]","cost"=>$nextTech[1],"process"=>0,"allowance"=>0];
        Country::where(["tag"=>$id])->update(["techList"=>json_encode($techList,JSON_UNESCAPED_UNICODE)]);
    }
    public function setTechAllowance(Request $request) {
        $id = $request->input("id");
        $tech = $request->input('tech');
        $cash = $request->input('allowance');
        $techList = json_decode(Country::where(["tag"=>$id])->first()->techList,true);
        $resource = json_decode(Country::where(["tag"=>$id])->first()->resource,true);
        foreach ($techList as $key=>$item) {
            if ($item['tech'] == $tech) {
                if($resource['energy']-$cash < 0){
                    return;
                } else {
                    $techList[$key]['allowance'] += $cash;
                    $resource['energy'] -= $cash;
                    break;
                }
            }
        }
        Country::where(["tag"=>$id])->update(["techList"=>json_encode($techList,JSON_UNESCAPED_UNICODE),"resource"=>json_encode($resource,JSON_UNESCAPED_UNICODE)]);
    }
    public function deleteTech(Request $request) {
        $id = $request->input("id");
        $tech = $request->input('tech');
        $techList = json_decode(Country::where(["tag" => $id])->first()->techList,true);
        foreach ($techList as $key => $item) {
            echo $tech,$item['tech'];
            if ($item['tech'] == $tech) {
                $resource = json_decode(Country::where(["tag"=>$id])->first()->resource,true);
                $resource['energy'] += $item['allowance'];
                var_dump($techList);
                array_splice($techList,$key,1);
                break;
            }
        }
        Country::where(["tag"=>$id])->update(["techList"=>json_encode($techList,JSON_UNESCAPED_UNICODE),"resource"=>json_encode($resource,JSON_UNESCAPED_UNICODE)]);
    }
    public function adminAddTech(Request $request) {
        $uid = $request->session()->get('uid');
        $user= UserController::GetInfo($uid);
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $request->input('country');
        $tech = $request->input('tech');
        if ($privilege <= 1) {
            $country = Country::where(["tag"=>$country])->first();
            $techs = json_decode($country->techs,true);
            $ModifierList = json_decode($country->ModifierList,true);
            $techs[] = $tech;
            $modifier = json_decode(Tech::where(["name"=>$tech])->first()->modifier,true);
            $ModifierList[] = ["name"=>$tech,"modifier"=>$modifier];
            $country->ModifierList = json_encode($ModifierList,JSON_UNESCAPED_UNICODE);
            $country->techs = json_encode($techs,JSON_UNESCAPED_UNICODE);
            $country->save();
        }
    }
    function techCount(){
        $country = Country::where(["tag"=>$this->tag])->first()->toArray();
        $ethicsM = $country['ethicsM'];
        $ethicsAM = $country['ethicsAM'];
        $techs = json_decode($country['techs'],true);
        $techList = json_decode($country['techList'],true);
        foreach ($techList as $key=>$item) {
            if ($item['process'] >= $item['cost']) {
                $ModifierList = json_decode($country['ModifierList'],true);
                $techs[] = $item['tech'];
                $modifier = json_decode(Tech::where(["name"=>$item['tech']])->first()->modifier,true);
                $ModifierList[] = ["name"=>$item['tech'],"modifier"=>$modifier];
                $country['ModifierList'] = json_encode($ModifierList,JSON_UNESCAPED_UNICODE);
                array_splice($techList,$key,1);
                break;
            }
            $process = random_int(100,1000);
            $process *= 1+$ethicsM/400;
            $process *= 1-$ethicsAM/400;
            $techList[$key]['process'] += $process;
        }
        $techs = json_encode($techs,JSON_UNESCAPED_UNICODE);
        $techList = json_encode($techList,JSON_UNESCAPED_UNICODE);
        Country::where(["tag"=>$this->tag])->update(["techList"=>$techList,"techs"=>$techs,"ModifierList"=>$country['ModifierList']]);
    }
    function modifierCount() {
        echo $this->tag;
        $country = Country::where(["tag"=>$this->tag])->first();
        $ModifierList = json_decode($country->ModifierList, true);
        $modifierIDs = Definition::get()->toArray();
        foreach($modifierIDs as $key=>$modifier) {
            if ($modifier['area'] == 'ethic') {
                array_splice($modifierIDs, $key,1);
            }
        }
        foreach ($modifierIDs as $modifier) {
            $name = $modifier['name'];
            $country->$name = 0;
        }
        foreach($ModifierList as $item)  {
            foreach($item['modifier'] as $key => $value) {
                $country->$key += $value;
            }
        }
        $country->save();
    }
    function countPower($id){
        $country = Country::where('tag', $id)->first();
        $resource = json_decode($country->resource,true);
        $eco = 0;
        foreach($resource as $key => $value) {
            if ($key == 'energy'||$key == 'grain'||$key == 'minerals') {
                $eco += 2*$value;
            } elseif ($key == 'consume_goods') {
                $eco += 4*$value;
            } elseif ($key == 'alloys') {
                $eco += 8*$value;
            } elseif ($key == 'gases'|| $key == 'motes' || $key == 'crystals') {
                $eco += 20*$value;
            } else {
                $eco += 40*$value;
            }
        }
        $fleets = Fleet::where(["owner"=>$id])->get()->toArray();
        $mili = 0;
        foreach ($fleets as $fleet) {
            $mili += $fleet['power'];
        }
        Country::where(["tag"=>$id])->update(["economyPower"=>$eco,"militaryPower"=>$mili]);
    }
    public function changeTax(Request $request) {
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid" => $uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        $id = $request->input('id');
        $type = $request->input('type');
        $tax = $request->input('tax');
        if (($privilege == 2 && $id == $country) || $privilege <= 1) {
            Country::where(["tag" =>$id])->update([$type=>$tax]);
        }
    }
    public function technologyPage(Request $request) {
        $uid = $request->session()->get('uid');
        $user= UserController::GetInfo($uid);
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        if($privilege == 0) {
            $country = Country::where(["tag"=>"GSK"])->first()->toArray();
        } elseif ($privilege == 2 || $privilege == 1) {
            $country = User::where("uid",$uid)->first()->country;
            $country = Country::where(["tag"=>$country])->first()->toArray();
        }
        $country['techs'] = json_decode($country['techs'],true);
        $country['planets'] = json_decode($country['planets'],true);
        $pops = Population::get()->toArray();
        $slots = 3;
        $country['techList'] = json_decode($country['techList'],true);
        $techArea = TechArea::get()->toArray();
        $techs = Tech::get()->toArray();
        foreach($techs as $key=>$tech) {
            if(in_array($tech['name'],$country['techs'])) {
                array_splice($techs,$key,1);
            }
        }
        return view("technology",["privilege"=>$privilege,"user"=>$user,
            "country"=>$country,"slots"=>$slots,"techArea"=>$techArea,"techs"=>$techs]);
    }
    public function mainFunction($id) {
        ini_set("display_errors", "On");
        ini_set("error_reporting", E_ALL);
        $planets = json_decode(Country::where('tag', $id)->first()->planets,true);
        $resource = json_decode(Country::where('tag', $id)->first()->resource,true);
        foreach($planets as $planet) {
            $p = Planet::where('owner', $id)->first();
            foreach($resource as $key => $value) {
                $resource[$key] = 0;
            }
            foreach($resource as $key => $value) {
                $resource[$key] += $p->$key;
            }
        }
        $ships = Ship::where('owner',$id)->get()->toArray();
        foreach($ships as $ship) {
            $upkeep = ShipType::where(["type"=>$ship["shipType"]])->first()->baseUpkeep;
            $resource['alloys'] -= $upkeep;
        }
        $stars = Star::where('owner',$id)->get()->toArray();
        foreach($stars as $star) {
            $star['resource'] = json_decode($star['resource'],true);
            foreach($star['resource'] as $key=>$value) {
                $resource[$key] += $value;
            }
        }
        $armys = Army::where('owner',$id)->get()->toArray();
        $resource['energy'] -= count($armys);
        Country::where('tag', $id)->update(["resource"=>json_encode($resource,JSON_UNESCAPED_UNICODE)]);
        $this->countPower($id);
        $this->techCount();
        $this->modifierCount();
    }
}
