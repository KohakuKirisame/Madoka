<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Definition;
use App\Models\Planet;
use App\Models\Tech;
use App\Models\TechArea;
use http\Env\Request;

class CountryController
{
    var $tag;
//    var $id,$name,$color,$capital,$energy,$stars,$planets,$financeList,$policyList;
//    var $techs,$techList,$ModifierList;

    function __construct($tag)
    {
        $this->tag = $tag;
    }
    public function chooseTech(Request $request) {
        $id = $request->input('id');
        $choose = $request->input('choose');
        $categorys = TechArea::where(["area"=>$choose])->toArray();
        $category = $categorys[array_rand($categorys,1)];
        $nextTechs = [];
        $techList = json_decode(Country::where(["tag"=>$id])->first()->techList,true);
        $techs = Tech::where(["category"=>$category])->toArray();
        foreach ($techs as $tech) {
            if (in_array($tech['proTech'],$techList)) {
                $nextTechs[] = [$tech['name'],$tech['cost']];
                break;
            }
        }
        $nextTech = $nextTechs[array_rand($nextTechs,1)];
        $techList[] = [["area"=>$choose,"category"=>$category,"tech"=>"$nextTech[0]","cost"=>$nextTech[1],"process"=>0,"allowance"=>0]];
        Country::where(["tag"=>$id])->update(["techList"=>json_decode($techList)]);
    }
    public function setTechAllowance(Request $request) {
        $id = $request->input("id");
        $area = $request->input('area');
        $cash = $request->input('cash');
        $techList = json_decode(Country::where(["tag"=>$id])->first()->techList);
        foreach ($techList as $item) {
            if ($item['area'] == $area) {
                $item['allowance'] = $cash;
                break;
            }
        }
        Country::where(["tag"=>$id])->update(["techList"=>json_decode($techList)]);
    }
    function techCount(){
        $country = Country::where(["tag"=>$this->tag])->first()->toArray();
        $ethicsM = $country['ethicsM'];
        $ethicsAM = $country['ethicsAM'];
        $techs = json_decode($country['techs'],true);
        $techList = json_decode($country['techList'],true);
        foreach ($techList as $item) {
            $process = random_int(10,100)*(1+($item['allowance']/$item['cost']));
            $process *= 1+$ethicsM/400;
            $process *= 1-$ethicsAM/400;
            $item['process'] += $process;
            if ($item['process'] >= $item['cost']) {
                $modifierList = json_decode($country->ModifierList,true);
                $techs[] = $item['name'];
                $modifier = json_decode(Tech::where(["name"=>$item['name']])->first()->modifier,true);
                $ModifierList[] = ["name"=>$item['name'],"modifier"=>$modifier];
                $country['ModifierList'] = json_encode($ModifierList,JSON_UNESCAPED_UNICODE);
                unset($item);
            }
        }
        $techs = json_encode($this->techs,JSON_UNESCAPED_UNICODE);
        $techList = json_encode($this->techList,JSON_UNESCAPED_UNICODE);
        Country::where(["tag"=>$this->tag])->update(["techList"=>$techList,"techs"=>$techs,"ModifierList"=>$country->ModifierList]);
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
            $country->$modifier['name'] = 0;
        }
        foreach($country['ModifierList'] as $modifier)  {
            foreach($modifier['modifier'] as $key => $value) {
                $country->$key += $value;
            }
        }
        $country->save();
    }
    public function mainFunction($id) {
        $self = Planet::where(["owner"=>$id])->fitst();
        $planets = json_decode($self->planets,true);
        foreach($planets as $value) {
            $planet = new PlanetController($value);
            $planet->searchTradeHub();
            $planet->districtCount();
            $planet->investDistrict();
            $planet->countRes();
            $planet->popGrowth();
            foreach ($planet['pops'] as $value2) {
                $pop = new PopController($value2);
                $pop->findJob();
                $pop->getNeeds();
                $pop->invest();
            }
        }
        $market = new MarketController($id);
        $market->countTrade();
        $market->priceCount();
        $this->techCount();
        $this->modifierCount();
    }
}
