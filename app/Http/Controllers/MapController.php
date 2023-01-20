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
            if ($privilege == 0) {
                $stars=Star::get()->toArray();
                $countries=Country::get()->toArray();
                return view("map",["stars"=>$stars,"countrys"=>$countries]);
            }
        }else {
            return redirect("https://kanade.nbmun.cn");
        }
    }

}
