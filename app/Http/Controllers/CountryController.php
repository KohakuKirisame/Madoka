<?php

namespace App\Http\Controllers;

use App\Models\Country;
use app\Models\Tech;
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
        $country = Country::where(["tag"=>$this->tag])->first();
        $ethicsM = $country->ethicsM;
        $ethicsAM = $country->ethicsAM;
        $techs = json_decode($country->techs,true);
        $techList = json_decode($country->techList,true);
        foreach ($techList as $item) {
            $process = random_int(10,100)*(1+($value2['allowance']/$value2['cost']));
            $process *= 1+$ethicsM/400;
            $process *= 1-$ethicsAM/400;
            $item['process'] += $process;
            if ($item['process'] >= $item['cost']) {
                unset($this->techList[$key]);
                $modifierList = json_decode($country->ModifierList,true);
                $techs[] = $item['name'];
                $modifier = json_decode(Tech::where(["name"=>$item['name']])->first()->modifier,true);
                $ModifierList[] = ["name"=>$item['name'],"modifier"=>$modifier];
                $country->ModifierList = json_encode($modifierList,JSON_UNESCAPED_UNICODE);
            }
        }
        $techs = json_encode($this->techs,JSON_UNESCAPED_UNICODE);
        $techList = json_encode($this->techList,JSON_UNESCAPED_UNICODE);
        Country::where(["tag"=>$this->tag])->update(["techList"=>$techList,"techs"=>$techs,"ModifierList"=>$country->ModifierList]);
    }
}
