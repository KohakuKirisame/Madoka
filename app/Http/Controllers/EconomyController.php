<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;

class EconomyController extends Controller {
    public function allMainFunction(Request $request) {
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        if ($privilege == 0) {
            $countrys = Country::get()->toArray();
            foreach ($countrys as $country) {
                $c = new CountryController($country['tag']);
                $c->mainFunction($country['tag']);
            }
        }
    }
}
