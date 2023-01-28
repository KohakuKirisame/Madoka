<?php

namespace App\Http\Controllers;

use App\Models\Army;
use App\Models\Country;
use App\Models\Fleet;
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
            $fleets = Fleet::get()->toArray();
            foreach ($fleets as $fleet) {
                $f = new MilitaryController();
                $f->moveCount("fleet",$fleet['id']);
            }
            $armys = Army::get()->toArray();
            foreach ($armys as $army) {
                $a = new MilitaryController();
                $a->moveCount("army",$army['id']);
            }
        }
    }
}
