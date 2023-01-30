<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Definition;
use App\Models\Population;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class UserController extends Controller {
    public function Auth(Request $request){
        if($request->session()->exists("uid")){
            return redirect("/");
        }
        $code=$request->input("code");
        $data=http_build_query(["f"=>"oauth_token","code"=>$code]);
        $options = array('http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $data,
            'timeout' => 15 * 60
        ));
        $context=stream_context_create($options);
        $result=file_get_contents($_ENV["REIMU_URL"]."/includes/query.php", false, $context);
        $result=json_decode($result,true);
        if(key_exists("err",$result)){
            return view("login_failed");
        }else{
            $request->session()->put(["uid"=>intval($result["uid"]),"token"=>$result["token"],"valid"=>time()+43200]);
            return redirect("/Dashboard");
        }
    }

    public function LogOut(Request $request){
        $request->session()->flush();
        return redirect("/Dashboard");
    }

    static public function IsAuthed(Request $request){
        if($request->session()->exists(["uid","token"])){
            return true;
        }else{
            return false;
        }
    }

    static public function GetInfo($uid_search){
        ini_set("allow_url_fopen","On");
        $uid=Session::get("uid");
        $token=Session::get("token");
        if(gettype($uid_search)=="integer"){
            $search=json_encode(array($uid_search));
        }elseif(gettype($uid_search)=="string"){
            $search=json_encode(array(intval($uid_search)));
        }elseif(gettype($uid_search)=="array"){
            $search=json_encode($uid_search);
        }

        $data=http_build_query(["f"=>"InfoJson","uid"=>$uid,"token"=>$token,"uid_search"=>$search]);
        $options = array('http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $data,
            'timeout' => 15 * 60,
        ));
        $context=stream_context_create($options);
        $result=file_get_contents($_ENV["REIMU_URL"]."/includes/query.php", false, $context);
        $result=json_decode($result,associative: true);

        return $result;
    }

    public function ShowDashboard(Request $request){
        $uid=$request->session()->get("uid");
        if(User::where("uid",$uid)->exists()) {
            $privilege = User::where("uid", $uid)->first()->privilege;
        }else{
            return redirect("https://kanade.nbmun.cn");
        }
        $user=$this->GetInfo($uid);
        if (key_exists("Err",$user)){
            return redirect("/Action/Logout");
        }
        if($privilege <= 1) {
            $country = Country::where(["tag"=>"GSK"])->first()->toArray();
        } elseif ($privilege == 2) {
            $country = User::where("uid",$uid)->first()->country;
            $country = Country::where(["tag"=>$country])->first()->toArray();
        } else {
            return redirect("/News");
        }
        $country['resource'] = json_decode($country['resource'],true);
        $country['atWarWith'] = json_decode($country['atWarWith'],true);
        foreach($country['atWarWith'] as $key=>$value) {
            $country['atWarWith'][$key] = [$value,Country::where(["tag"=>$value])->first()->name];
        }
        $country['alliedWith'] = json_decode($country['alliedWith'],true);
        foreach($country['alliedWith'] as $key=>$value) {
            $country['alliedWith'][$key] = [$value,Country::where(["tag"=>$value])->first()->name];
        }
        $country['species'] = json_decode($country['species'],true);
        $country['techs'] = json_decode($country['techs'],true);
        $country['ModifierList'] = json_decode($country['ModifierList'],true);
        foreach ($country['ModifierList'] as $key=>$modifiers) {
            foreach ($modifiers['modifier'] as $key2=>$value) {
                $modifierName = Definition::where(["name"=>$key2])->first()->localization;
                $country['ModifierList'][$key]['modifier'][$modifierName] =$value;
                unset($country['ModifierList'][$key]['modifier'][$key2]);
            }
        }
        $country['planets'] = json_decode($country['planets'],true);
        $pops = Population::get()->toArray();
        $species = [];
        foreach ($pops as $pop) {
            if (in_array($pop['position'],$country['planets'])) {
                if (!in_array($pop['species'],$species)) {
                    $species[] = $pop['species'];
                }
            }
        }
        $Species = [];
        foreach ($country['species'] as $spe) {
            if (!in_array($spe['name'],$Species)) {
                $Species[] = $spe['name'];
            }
        }
        foreach ($species as $spe) {
            if (!in_array($spe, $Species)) {
                $country['species'][] = ["name"=>$spe,"right"=>0];
            }
        }
        Country::where(["tag"=>$country['tag']])->update(["species"=>json_encode($country['species'],JSON_UNESCAPED_UNICODE )]);
        return view("dashboard",["privilege"=>$privilege,"user"=>$user,
            "country"=>$country]);
    }
}
