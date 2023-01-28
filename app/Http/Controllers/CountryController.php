<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Definition;
use App\Models\Planet;
use App\Models\Population;
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
            if (in_array($tech['preTech'],$techFin)) {
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
        $energy = Country::where(["tag"=>$id])->first()->energy;
        foreach ($techList as $key=>$item) {
            if ($item['tech'] == $tech) {
                if($energy < $cash){
                    return;
                } else {
                    $techList[$key]['allowance'] = $cash;
                    $energy -= $cash;
                    break;
                }
            }
        }
        Country::where(["tag"=>$id])->update(["techList"=>json_encode($techList,JSON_UNESCAPED_UNICODE),"energy"=>$energy]);
    }
    public function deleteTech(Request $request) {
        $id = $request->input("id");
        $tech = $request->input('tech');
        $techList = json_decode(Country::where(["tag" => $id])->first()->techList,true);
        foreach ($techList as $key => $item) {
            echo $tech,$item['tech'];
            if ($item['tech'] == $tech) {
                var_dump($techList);
                unset($techList[$key]);
                break;
            }
        }
        Country::where(["tag"=>$id])->update(["techList"=>json_encode($techList,JSON_UNESCAPED_UNICODE)]);
    }
    function techCount(){
        $country = Country::where(["tag"=>$this->tag])->first()->toArray();
        $ethicsM = $country['ethicsM'];
        $ethicsAM = $country['ethicsAM'];
        $techs = json_decode($country['techs'],true);
        $techList = json_decode($country['techList'],true);
        foreach ($techList as $item) {
            if ($item['process'] >= $item['cost']) {
                $modifierList = json_decode($country['ModifierList'],true);
                $techs[] = $item['tech'];
                $modifier = json_decode(Tech::where(["name"=>$item['tech']])->first()->modifier,true);
                $ModifierList[] = ["name"=>$item['tech'],"modifier"=>$modifier];
                $country['ModifierList'] = json_encode($ModifierList,JSON_UNESCAPED_UNICODE);
                unset($item);
                break;
            }
            $process = random_int(10,100)*(1+($item['allowance']/$item['cost']));
            $process *= 1+$ethicsM/400;
            $process *= 1-$ethicsAM/400;
            $item['process'] += $process;
        }
        $techs = json_encode($techs,JSON_UNESCAPED_UNICODE);
        $techList = json_encode($techList,JSON_UNESCAPED_UNICODE);
        Country::where(["tag"=>$this->tag])->update(["techList"=>$techList,"techs"=>$techs,"ModifierList"=>$country['ModifierList']]);
    }
    function modifierCount() {
        $country = Country::where(["tag"=>$this->tag])->first();
        $ModifierList = json_decode($country->ModifierList, true);
        $modifierIDs = Definition::get()->toArray();
        foreach($modifierIDs as $modifier) {
            if ($modifier['area'] == 'ethic') {
                unset($modifier);
                array_values($modifierIDs);
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
        if($privilege <= 1) {
            $country = Country::where(["tag"=>"GSK"])->first()->toArray();
        } elseif ($privilege == 2) {
            $country = User::where("uid",$uid)->first()->country;
            $country = Country::where(["tag"=>$country])->first()->toArray();
        }
        $country['techs'] = json_decode($country['techs'],true);
        $country['planets'] = json_decode($country['planets'],true);
        $pops = Population::get()->toArray();
        $slots = 1;
        foreach($pops as $pop) {
            if(in_array($pop['position'],$country['planets'])) {
                if($pop['class'] == 'low') {
                    $slots += 0.01;
                } elseif ($pop['class'] == 'mid') {
                    $slots += 0.02;
                } else {
                    $slots += 0.03;
                }
            }
        }
        $country['techList'] = json_decode($country['techList'],true);
        $techArea = TechArea::get()->toArray();
        return view("technology",["privilege"=>$privilege,"user"=>$user,
            "country"=>$country,"slots"=>$slots,"techArea"=>$techArea]);
    }
    public function mainFunction($id) {
        ini_set("display_errors", "On");
        ini_set("error_reporting", E_ALL);
        $planets = json_decode(Country::where('tag', $id)->first()->planets,true);
        foreach($planets as $value) {
            $planet = new PlanetController($value);
            echo($value."s1");
            $planet->searchTradeHub();
            echo($value."s2");
            $planet->districtCount();
            echo($value."s3");
            $planet->investDistrict();
            echo($value."s4");
            $planet->countRes();
            echo($value."s5");
            $planet->popGrowth();
            echo "星球遍历",$value;
            foreach ($planet->pops as $value2) {
                $pop = new PopController($value2);
                $pop->findJob();
                $pop->getNeeds();
                $pop->invest();
                echo "人口遍历",$value2;
            }
        }
        $market = new MarketController($id);
        $market->countTrade();
        $market->priceCount();
        $this->techCount();
        $this->modifierCount();
    }
}
