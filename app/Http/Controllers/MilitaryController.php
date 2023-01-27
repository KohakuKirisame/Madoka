<?php

namespace App\Http\Controllers;

use App\Models\Army;
use App\Models\Country;
use App\Models\Definition;
use App\Models\Fleet;
use App\Models\Ship;
use App\Models\ShipComputer;
use App\Models\ShipType;
use App\Models\Star;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MilitaryController extends Controller{

    public function spaceBattle() {
        $fleets = func_get_args();
        $flags = array();
        foreach ($fleets as $key => $value) {
            $result = Fleet::where(["id" => $value])->get();  //$conn->query("SELECT owner FROM Fleet WHERE id='$value'");
            $owner = $result->first()->owner;
            if (in_array($owner, $flags)) {
                continue;
            } else {
                array_push($flags, $owner);
            }
        }
        for ($i = 0; $i < count($fleets); $i++) {
            $fleets[$i] = array($fleets[$i]);
            for ($j = $i + 1; $j < count($fleets); $j++) {
                $id1 = $fleets[$i][0];
                $result = Fleet::where(["id" => $id1])->get();
                $owner1 = $result->first()->owner;
                $id2 = $fleets[$j][0];
                $result = Fleet::where(["id" => $id2])->get();
                $owner2 = $result->first()->owner;
                $result = Country::where(["id" => $owner1])->get(); //$conn->query("SELECT alliedWith FROM Country WHERE id='$owner1'");
                $ally = json_decode($result->first()->alliedWith, true);
                if (in_array($owner2, $ally)) {
                    array_push($fleets[$i], $fleets[$j]);
                    unset($fleets[$j]);
                    $fleets = array_values($fleets);
                }
            }
        }
        $dataArr = array();
        foreach ($fleets as $key => $value) {
            $dataArr[$key] = array();
            foreach ($value as $key2 => $value2) {
                $hull = $PDamage = $EDamage = $shield = $armor = $evasion = $speed = $ships = $disengageChance = 0;
                $computer = '';
                $data = Fleet::where(["id" => $value2])->first(); //$conn->query("SELECT * FROM Fleet WHERE id='$value2'");
                //$conn->query("SELECT ships,computer,hull,PDamage,EDamage,shield,armor,evasion,speed,disengageChance FROM Fleet WHERE id='$value2'");
                $ships = count(json_decode($data['ships'], true));
                $hull = $data->hull;
                $PDamage = $data->PDamage;
                $EDamage = $data->EDamage;
                $shield = $data->shield;
                $armor = $data->armor;
                $evasion = $data->evasion;
                $speed = $data->speed;
                $disengageChance = $data->disengageChance;
                $computer = $data->computer;
                //////

                //////
                array_push($dataArr[$key], array("hull" => $hull,
                    "fullHull" => $hull,
                    "PDamage" => $PDamage,
                    "EDamage" => $EDamage,
                    "shield" => $shield,
                    "armor" => $armor,
                    "evasion" => $evasion,
                    "speed" => $speed,
                    "ships" => $ships,
                    "disengageChance" => $disengageChance,
                    "computer" => $computer));
            }
        }
//////////
        $result = Fleet::get(["id", "owner"]);//$conn->query("SELECT id,owner FROM Fleet");
        $fleetsOwner = [];
        foreach ($result as $value) {
            $fleetsOwner[] = array($value->id, $value->owner);
        }
        $result = Country::get(["id", "shipComputerModifier"]);//$conn->query("SELECT id,shipComputerModifier FROM Country");
        $computerModifier = [];
        foreach ($result as $value) {
            $computerModifier[] = array($value->id, $value->owner);
        }
        $result = ShipComputer::get(["id", "selfModifier", "computerAnti"]); //$conn->query("SELECT id,selfModifier,computerAnti FROM ShipComputers");
        $computers = [];
        foreach ($result as $value) {
            $computers[] = array($value->id, $value->selfModifier, $value->computerAnti);
        }
        foreach ($computers as $key => $value) {
            $computers[$key][1] = json_decode($computers[$key][1], true);
        }
//////////
        while (count($dataArr) > 1) {
            $damageBuffer = array();
            foreach ($dataArr as $key => $value) {
                $damageBuffer[$key] = array();
                foreach ($value as $key2 => $value2) {
                    if ($dataArr[$key][$key2]['computer'] == 1 && $dataArr[$key][$key2]['hull'] < 0.2 * $dataArr[$key][$key2]['fullHull']) {
                        $dataArr[$key][$key2]['hull'] = 0;
                        array_push($damageBuffer[$key], array('PDamage' => $dataArr[$key][$key2]['PDamage'] * 2,
                            'EDamage' => $dataArr[$key][$key2]['EDamage'],
                            'computer' => $dataArr[$key][$key2]['computer']));
                    } else {
                        array_push($damageBuffer[$key], array('PDamage' => $dataArr[$key][$key2]['PDamage'],
                            'EDamage' => $dataArr[$key][$key2]['EDamage'],
                            'computer' => $dataArr[$key][$key2]['computer']));
                    }
                }
            }
            foreach ($dataArr as $key => $value) {// 循环国
                $enemyShips = 0;
                foreach ($dataArr as $key2 => $value2) {
                    if ($key2 == $key) {
                        continue;
                    } else {
                        foreach ($value2 as $key3 => $value3) {
                            $enemyShips += $value3['ships'];
                        }
                    }
                }
                foreach ($dataArr as $key2 => $value2) {// 循环除我之外的舰队
                    if ($key2 == $key) {
                        continue;
                    }
                    foreach ($value2 as $key3 => $value3) {// 敌人联合内部
                        foreach ($damageBuffer as $key4 => $value4) {// 子弹国家
                            if ($key4 == $key2) {
                                continue;
                            }
                            foreach ($value4 as $key5 => $value5) {// 子弹内部
                                $PDamage = $value5['PDamage'];
                                $EDamage = $value5['EDamage'];
                                ////////////////////////////////
                                $id1 = $fleets[$key4][$key5];//子弹国
                                for ($i = 0; $i < count($fleetsOwner); $i++) {
                                    if ($id1 == $fleetsOwner[$i][0]) {
                                        $owner1 = $fleetsOwner[$i][1];
                                    }
                                }
                                for ($i = 0; $i < count($computerModifier); $i++) {
                                    if ($owner1 == $computerModifier[$i][0]) {
                                        $shipComputerModifier1 = $computerModifier[$i][1];
                                    }
                                }

                                $id2 = $fleets[$key2][$key3];//受伤国
                                for ($i = 0; $i < count($fleetsOwner); $i++) {
                                    if ($id2 == $fleetsOwner[$i][0]) {
                                        $owner2 = $fleetsOwner[$i][1];
                                    }
                                }
                                for ($i = 0; $i < count($computerModifier); $i++) {
                                    if ($owner2 == $computerModifier[$i][0]) {
                                        $shipComputerModifier2 = $computerModifier[$i][1];
                                    }
                                }
                                ///////////
                                $computer1 = $value5['computer'];
                                for ($i = 0; $i < 4; $i++) {
                                    if ($computer1 == $computers[$i][0]) {
                                        if ($computers[$i][2] == $dataArr[$key2][$key3]['computer']) {
                                            $shipComputerModifier2 *= (1 - 0.1 * $shipComputerModifier1);
                                        }
                                    }
                                }
                                $modifier1 = array();
                                for ($i = 0; $i < 4; $i++) {
                                    if ($computer1 == $computers[$i][0]) {
                                        $modifier1 == $computers[$i][1];
                                    }
                                }
                                foreach ($modifier1 as $key6 => $value6) {// 子弹修正
                                    $modifierKey = Definition::where(['name' => $key6])->first()->modifierKey;//$conn->query("SELECT modifierKey FROM Definitions WHERE name='$key6'")
                                    if ($modifierKey == 'PDamage') {
                                        $PDamage *= 1 + $value6 + $shipComputerModifier1;
                                    } elseif ($modifierKey == 'EDamage') {
                                        $EDamage *= 1 + $value6 + $shipComputerModifier1;
                                    }
                                }
                                $computer2 = $dataArr[$key2][$key3]['computer'];
                                $modifier2 = array();
                                for ($i = 0; $i < 4; $i++) {
                                    if ($computer2 == $computers[$i][0]) {
                                        $modifier2 == $computers[$i][1];
                                    }
                                }
                                foreach ($modifier2 as $key6 => $value6) {// 受伤国加成
                                    $modifierKey = Definition::where(['name' => $key6])->first()->modifierKey;
                                    $modifierKey = $result->fetch_row()[0];
                                    $dataArr[$key2][$key3][$modifierKey] *= 1 + $value6 + $shipComputerModifier2;
                                }
                                ///////开打////////
                                if ($dataArr[$key2][$key3]['shield'] > 0) {
                                    $dataArr[$key2][$key3]['shield'] -= ($PDamage * 0.5 + $EDamage * 1.5) * (1 - $dataArr[$key2][$key3]['evasion']) * $dataArr[$key2][$key3]['ships'] / $enemyShips;
                                    echo $fleets[$key2][$key3], '|', $dataArr[$key2][$key3]['shield'], '|', $dataArr[$key2][$key3]['armor'], '|', $dataArr[$key2][$key3]['hull'], "\n";
                                } else {
                                    if ($dataArr[$key2][$key3]['armor'] > 0) {
                                        $dataArr[$key2][$key3]['armor'] -= ($PDamage * 1.5 + $EDamage * 0.5) * (1 - $dataArr[$key2][$key3]['evasion']) * $dataArr[$key2][$key3]['ships'] / $enemyShips;
                                        echo $fleets[$key2][$key3], '|', $dataArr[$key2][$key3]['shield'], '|', $dataArr[$key2][$key3]['armor'], '|', $dataArr[$key2][$key3]['hull'], "\n";
                                    } else {
                                        if ($dataArr[$key2][$key3]['hull'] > 0) {
                                            if ($dataArr[$key2][$key3]['hull'] <= 0.5 * $dataArr[$key2][$key3]['fullHull']) {
                                                $damage = ($PDamage * 1 + $EDamage * 1) * (1 - $dataArr[$key2][$key3]['evasion']) * $dataArr[$key2][$key3]['ships'] / $enemyShips;
                                                $disengage = ($damage / $dataArr[$key2][$key3]['hull'] * 1.5 * $dataArr[$key2][$key3]['disengageChance']);
                                                echo '~', $disengage, '~';
                                                echo $fleets[$key2][$key3], '|', $dataArr[$key2][$key3]['shield'], '|', $dataArr[$key2][$key3]['armor'], '|', $dataArr[$key2][$key3]['hull'], "\n";
                                                if ($disengage > 1) {
                                                    unset($dataArr[$key2][$key3]);
                                                    array_values($dataArr[$key2]);
                                                    if (count($dataArr[$key2]) == 0) {
                                                        unset($dataArr[$key2]);
                                                        array_values($dataArr);
                                                    }
                                                    ///撤退///
                                                    $id = $fleets[$key2][$key3];
                                                    for ($i = 0; $i < count($fleetsOwner); $i++) {
                                                        if ($id == $fleetsOwner[$i][0]) {
                                                            $owner == $fleetsOwner[$i][1];
                                                        }
                                                    }
                                                    $owner = $result->fetch_row()[0];
                                                    $cap = Country::where(["id" => $owner])->first()->capital;//$conn->query("SELECT capital FROM Country WHERE id='$owner'")
                                                    $result = Fleet::where(["id" => $id])->update(["position" => $cap]);//$conn->query("UPDATE Fleet SET position=$cap WHERE id=$id");
                                                    break 4;
                                                } else {
                                                    $random = random_int(1, 100);
                                                    if ($random <= 100 * $disengage) {
                                                        unset($dataArr[$key2][$key3]);
                                                        array_values($dataArr[$key2]);
                                                        if (count($dataArr[$key2]) == 0) {
                                                            unset($dataArr[$key2]);
                                                            array_values($dataArr);
                                                        }
                                                        ///撤退///
                                                        $id = $fleets[$key2][$key3];
                                                        for ($i = 0; $i < count($fleetsOwner); $i++) {
                                                            if ($id == $fleetsOwner[$i][0]) {
                                                                $owner == $fleetsOwner[$i][1];
                                                            }
                                                        }
                                                        $cap = Country::where("id", "=", $owner)->first()->capital; //$conn->query("SELECT capital FROM Country WHERE id='$owner'")
                                                        $result = Fleet::where(["id" => $id])->update(["position" => $cap]);
                                                        break 4;
                                                    }
                                                }
                                            } else {
                                                $dataArr[$key2][$key3]['hull'] -= ($PDamage * 1 + $EDamage * 1) * (1 - $dataArr[$key2][$key3]['evasion']) * $dataArr[$key2][$key3]['ships'] / $enemyShips;
                                                echo $fleets[$key2][$key3], '|', $dataArr[$key2][$key3]['shield'], '|', $dataArr[$key2][$key3]['armor'], '|', $dataArr[$key2][$key3]['hull'], "\n";
                                            }
                                        } else {
                                            unset($dataArr[$key2][$key3]);
                                            array_values($dataArr[$key2]);
                                            if (count($dataArr[$key2]) == 0) {
                                                unset($dataArr[$key2]);
                                                array_values($dataArr);
                                            }
                                            ///撤退///
                                            $id = $fleets[$key2][$key3];
                                            for ($i = 0; $i < count($fleetsOwner); $i++) {
                                                if ($id == $fleetsOwner[$i][0]) {
                                                    $owner == $fleetsOwner[$i][1];
                                                }
                                            }
                                            $cap = Country::where("id", "=", $owner)->first()->capital; //$conn->query("SELECT capital FROM Country WHERE id='$owner'")
                                            $result = Fleet::where(["id" => $id])->update(["position" => $cap]);
                                            break 4;
                                        }
                                    }
                                }
                                /////
                                $computer2 = $dataArr[$key2][$key3]['computer'];
                                $modifier2 = array();
                                for ($i = 0; $i < 4; $i++) {
                                    if ($computer2 == $computers[$i][0]) {
                                        $modifier2 == $computers[$i][1];
                                    }
                                }
                                foreach ($modifier2 as $key6 => $value6) {// 受伤国加成
                                    $modifierKey = Definition::where(['name' => $key6])->first()->modifierKey;
                                    $dataArr[$key2][$key3][$modifierKey] *= 1 + $value6 + $shipComputerModifier2;
                                }
                                /////
                            }
                        }
                    }
                }
            }
        }
        foreach ($dataArr as $key => $value) {
            foreach ($value as $key2 => $value2) {
                while ($value2['ships'] > 1) {
                    foreach ($fleets[$key] as $key3 => $value3) {
                        $f = readFleet($value2);
                        while (count($f->ships) > 1) {
                            $shipKey = array_rand($f->ships, 1);
                            $shipID = $f->ships[$shipKey];
                            unset($f->ships[$shipKey]);
                            array_values($f->ships);
                            $ships = json_encode($f->ships, JSON_UNESCAPED_UNICODE);
                            Fleet::where(["id" => $value2])->update(["ships" => $ships]);//$conn->query("UPDATE Fleet SET ships=$ships WHERE id=$value2");
                            Ship::where(["id" => $shipID])->delete();//$conn->query("DELETE FROM Ships WHERE id=$shipID");
                        }
                    }
                }
            }
        }
    }
    public function armyBattle($army1, $army2) {
        $army1 = Army::where(["id" => $army1])->first()->toArray();
        $army2 = Army::where(["id" => $army2])->first()->toArray();
        $country1 = Country::where(["tag" => $army1['owner']])->first()->toArray();
        $country2 = Country::where(["tag" => $army2['owner']])->first()->toArray();
        $army1FullHP = $army1['HP']*(1+($country1['armyHPModifier']));
        $army1['HP'] = $army1FullHP;
        $army1['damage'] *= 1+($country1['armyDamageModifier']);
        $army2FullHP = $army2['HP']*(1+($country2['armyHPModifier']));
        $army2['HP'] = $army2FullHP;
        $army2['damage'] *= 1+($country2['armyDamageModifier']);
        while ($army1['HP']>0 && $army2['HP']>0) {
            $army2['HP'] -= $army1['damage'];
            $army1['HP'] -= $army2['damage'];
            $army1['quantity'] = round($army1['HP']/$army1FullHP);
            $army1['damage'] = $army1['quantity']*10*(1+($country1['armyDamageModifier']));
            $army2['quantity'] = round($army2['HP']/$army2FullHP);
            $army2['damage'] = $army2['quantity']*10*(1+($country2['armyDamageModifier']));
        }
        if ($army1['HP'] <= 0 && $army1['quantity'] <= 0) {
            Army::where(["id" => $army1])->delete();
        } else {
            $army1['HP'] = $army1['quantity']*100*(1+($country1['armyHPModifier']));
            Army::where(["id" => $army1])->update(["HP"=>$army1['HP'],"damage"=>$army1['damage'],'quantity'=>$army1['quantity']]);
        }
        if ($army2['HP'] <= 0 && $army2['quantity'] <= 0) {
            Army::where(["id" => $army2])->delete();
        } else {
            $army2['HP'] = $army2['quantity']*100*(1+($country2['armyHPModifier']));
            Army::where(["id" => $army2])->update(["HP"=>$army2['HP'],"damage"=>$army2['damage'],"quantity"=>$army2['quantity']]);
        }
    }
    public function fleetCount($id){
        $fleet = Fleet::where(["id" => $id])->first()->toArray();
        $fleet['ships'] = json_decode($fleet['ships'],true);
        $hull = $PDamage = $EDamage = $shield = $armor = $evasion = $speed = $commandPoints = $disengageChance = 0;
        foreach ($fleet['ships'] as $key => $value) {
            $shipType = Ship::where(["id" => $value])->first()->shipType;
            $data = ShipType::where(["type" => $shipType])->first()->toArray();
            $commandPoints += $data['commandPoints'];
            $hull += $data['baseHull'];
            $PDamage += $data['basePDamage'];
            $EDamage += $data['baseEDamage'];
            $shield += $data['baseShield'];
            $armor += $data['baseArmor'];
            $evasion += $data['baseEvasion']*$data['commandPoints'];
            $speed += $data['baseSpeed']*$data['commandPoints'];
            $disengageChance += $data['disengageChance']*$data['commandPoints'];
        }
        $evasion = $evasion/$commandPoints;
        $speed = $speed/$commandPoints;
        $disengageChance = $disengageChance/$commandPoints;

        $weaponArr = array($fleet['weaponA'],$fleet['weaponB']);
        $weapon1 = $weapon2 = 0;
        foreach ($weaponArr as $key => $value) {
            if ($value == 1) {
                $weapon1 += 1;
            }
            else {
                $weapon2 += 1;
            }
        }
        $EDamage *= $weapon1;
        $armor *= 1+($weapon1*0.1);
        $PDamage *= $weapon2;
        $shield *= 1+($weapon2*0.1);
        $owner = $fleet['owner'];
        $data = Country::where(["tag" => $owner])->first()->toArray();
        $hull *= 1+$data['shipHullModifier'];
        $PDamage *= 1+$data['shipPDamageModifier'];
        $EDamage *= 1+$data['shipEDamageModifier'];
        $shield *= 1+$data['shipShieldModifier'];
        $armor *= 1+$data['shipArmorModifier'];
        $evasion *= 1+$data['shipEvasionModifier'];
        $speed *= 1+$data['shipSpeedModifier'];
        $disengageChance *=1+$data['shipDisengageChanceModifier'];

        $fleet['hull'] = $hull;
        $fleet['PDamage'] = $PDamage;
        $fleet['EDamage'] = $EDamage;
        $fleet['shield'] = $shield;
        $fleet['armor'] = $armor;
        $fleet['evasion'] = $evasion;
        $fleet['speed'] = $speed;
        $fleet['disengageChance'] = $disengageChance;
        $id = $fleet['id'];
        Fleet::where('id', $id)->update(["hull"=>$hull,"PDamage"=>$PDamage,"EDamage"=>$EDamage,
            "shield"=>$shield,"armor"=>$armor,"evasion"=>$evasion,"speed"=>$speed,"disengageChance"=>$disengageChance]);
    }

    public function militaryPage(Request $request){
        $uid = $request->session()->get('uid');
        $user= UserController::GetInfo($uid);
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        if ($privilege <= 1) {
            $fleets = Fleet::get()->toArray();
            $ftls = ["超空间引擎","曲率引擎"];
            $armys = Army::get()->toArray();
        } elseif ($privilege == 2) {
            $fleets = Fleet::where(["owner"=>$country])->get()->toArray();
            $ftls = ["超空间引擎",];
            $techs = json_decode(Country::where(["tag"=>$country])->first()->techs,true);
            if (in_array("曲率引擎",$techs)) {
                $ftls[] = "曲率引擎";
            }
            $armys = Army::where(["owner"=>$country])->get()->toArray();
        } else {
            return redirect('/Dashboard');
        }
        foreach ($fleets as $key => $fleet) {
            $fleet['computer'] = ShipComputer::where(["id"=>$fleet['computer']])->first()->localization;
            $fleet['position'] = Star::where(["id"=>$fleet['position']])->first()->name;
            $fleet['ships'] = json_decode($fleet['ships'], true);
            $fleet['ships'] = count($fleet['ships']);
            if ($fleet['ftl'] == 0) {
                $fleet['ftl'] = '超空间引擎';
            } elseif ($fleet['ftl'] == 1) {
                $fleet['ftl'] = '曲率引擎';
            }
            if ($fleet['weaponA'] == 1) {
                $fleet['weaponA'] = '能量武器';
            } else {
                $fleet['weaponA'] = '动能武器';
            }
            if ($fleet['weaponB'] == 1) {
                $fleet['weaponB'] = '能量武器';
            } else {
                $fleet['weaponB'] = '动能武器';
            }
            $fleets[$key] = $fleet;
        }
        foreach ($armys as $army) {
            $army['position'] = Star::where(["id"=>$army['position']])->first()->name;
        }
        $computers = ShipComputer::get()->toArray();
        $shipTypes = ShipType::get()->toArray();
        return view('military', ['user' => $user,"privilege"=>$privilege,"country"=>$country,
            'fleets'=>$fleets,"ftls"=>$ftls,"computers"=>$computers,"shipTypes"=>$shipTypes,
            'armys'=>$armys]);
    }
    public function readFleet(Request $request) {
        $id = $request->input('id');
        $fleet = Fleet::where(["id"=>$id])->first();
        $ships = json_decode($fleet->ships, true);
        $shipList = [];
        foreach ($ships as $ship) {
            $shipData = Ship::where(["id"=>$ship])->first();
            $type = $shipData->shipType;
            $name = ShipType::where(["type"=>$type])->first()->name;
            $shipList[] = [$shipData->id,$shipData->name,$name];
        }
        $output = ["name"=>$fleet->name,"hull"=>$fleet->hull,"EDamage"=>$fleet->EDamage,"PDamage"=>$fleet->PDamage,
                "armor"=>$fleet->armor,"shield"=>$fleet->shield,"evasion"=>$fleet->evasion,"speed"=>$fleet->speed,
                "shipList"=>$shipList,"weaponA"=>$fleet->weaponA,"weaponB"=>$fleet->weaponB];
        $output = json_encode($output);
        return $output;
    }
    public function changeFleetName(Request $request) {
        $id = $request->input('id');
        $name = $request->input("name");
        Fleet::where('id', $id)->update(["name"=>$name]);
    }
    public function changeShipName(Request $request) {
        $id = $request->input('id');
        $name = $request->input("name");
        Ship::where('id', $id)->update(["name"=>$name]);
    }
    public function changeFleetComputer(Request $request) {
        $id = $request->input('id');
        $computer = $request->input("computer");
        Fleet::where('id', $id)->update(["computer"=>$computer]);
    }
    public function changeFleetFTL(Request $request) {
        $id = $request->input('id');
        $ftl = $request->input("ftl");
        if ($ftl == "超空间引擎") {
            $ftl = 0;
        } else {
            $ftl = 1;
        }
        Fleet::where('id', $id)->update(["ftl"=>$ftl]);
    }
    public function adminNewShip(Request $request) {
        $id = $request->input('id');
        $type = $request->input('type');
        $uid = $request->session()->get('uid');
        $privilege = User::where(["uid"=>$uid])->first()->privilege;
        $fleet = Fleet::where(["id"=>$id])->first();
        if ($privilege <=1 ) {
            $ship = new Ship();
            $ship->name = $type;
            $ship->owner = $fleet->owner;
            $ship->shipType = $type;
            $ship->save();
            $ships = json_decode($fleet->ships,true);
            $ships[] = $ship->id;
            $ships = json_encode($ships,JSON_UNESCAPED_UNICODE);
            Fleet::where('id',$id)->update(["ships"=>$ships]);
        }
        $this->fleetCount($id);
    }
    public function getFleets(Request $request) {
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        if ($privilege == 0) {
            $fleets = Fleet::get()->toArray();
        }
        else {
            $fleets = Fleet::where(["owner"=>$country])->get()->toArray();
        }
        $fleets = json_encode($fleets,JSON_UNESCAPED_UNICODE);
        return $fleets;
    }
    public function fleetMerge(Request $request) {
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        $fleet1 = Fleet::where(["id"=>$request->input('id1')])->first()->toArray();
        $fleet2 = Fleet::where(["id"=>$request->input('id2')])->first()->toArray();
        if (($privilege == 2 && $fleet1['owner'] == $country && $fleet2['owner'] == $country) || $privilege <= 1) {
            $fleet1['ships'] = json_decode($fleet1['ships'], true);
            $fleet2['ships'] = json_decode($fleet2['ships'], true);
            foreach ($fleet2['ships'] as $value) {
                $fleet1['ships'][] = $value;
            }
            $this->fleetCount($fleet1['id']);
            $fleet1['ships'] = json_encode($fleet1['ships'], JSON_UNESCAPED_UNICODE);
            Fleet::where(["id"=>$fleet2['id']])->delete();
            Fleet::where(["id"=>$fleet1['id']])->update(["ships"=>$fleet1['ships']]);
        }
    }
    public function shipTrans(Request $request) {
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        $fleet1 = Fleet::where(["id"=>$request->input('f1')])->first()->toArray();
        $fleet2 = Fleet::where(["id"=>$request->input('f2')])->first()->toArray();
        $ship = $request->input('id');
        if (($privilege == 2 && $fleet1['owner'] == $country && $fleet2['owner'] == $country) || $privilege <= 1) {
            $fleet1['ships'] = json_decode($fleet1['ships'], true);
            $fleet2['ships'] = json_decode($fleet2['ships'], true);
            if (in_array($ship,$fleet1['ships'])) {
                $fleet2['ships'][] = $ship;
                foreach($fleet1['ships'] as $key => $value) {
                    if ($ship == $value) {
                        unset($fleet1['ships'][$key]);
                        array_values($fleet1['ships']);
                        break;
                    }
                }
            }
        }
        $this->fleetCount($fleet1['id']);
        $this->fleetCount($fleet2['id']);
        $fleet1['ships'] = json_encode($fleet1['ships'], JSON_UNESCAPED_UNICODE);
        $fleet2['ships'] = json_encode($fleet2['ships'], JSON_UNESCAPED_UNICODE);
        Fleet::where(["id"=>$fleet1['id']])->update(["ships"=>$fleet1['ships']]);
        Fleet::where(["id"=>$fleet2['id']])->update(["ships"=>$fleet2['ships']]);
    }
    public function fleetDelete(Request $request) {
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        $fleet1 = Fleet::where(["id"=>$request->input('id')])->first()->toArray();
        if (($privilege == 2 && $fleet1['owner'] == $country) || $privilege <= 1) {
            $fleet1['ships'] = json_decode($fleet1['ships'], true);
            foreach ($fleet1['ships'] as $ship) {
                Ship::where(["id"=>$ship])->delete();
            }
            Fleet::where(["id"=>$fleet1['id']])->delete();
        }
    }
    public function changeArmyName(Request $request) {
        $id = $request->input('id');
        $name = $request->input("name");
        Army::where('id', $id)->update(["name"=>$name]);
    }
    public function moveArmy(Request $request) {
        $id = $request->input('id');
        $targetStar = $request->input("target");
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        $army = Fleet::where(["id"=>$id])->first()->toArray();
        if (($privilege == 2 && $army['owner'] == $country) || $privilege <= 1) {
            $start = $army['position'];
            $hyperLanes = Star::where("id", $start)->first()->hyperlane;
            $hyperLanes = json_decode($hyperLanes, true);
            $queue = [];
            $previousStar = [$start, 0, 0];
            $routeFinder = [[$start, 0],];
            $visited = [$start,];
            $isReached = false;
            while (true) {
                foreach ($hyperLanes as $hyperLane) {
                    if (!in_array($hyperLane["to"], $visited)) {
                        $queue[] = [$hyperLane["to"], $previousStar[1] + 1, $previousStar[0]];
                        $routeFinder[] = [$hyperLane["to"], $previousStar[0]];
                        $visited[] = $hyperLane["to"];
                    }
                    if (in_array($hyperLane["to"], $targetStar)) {
                        $isReached = true;
                        $target = $hyperLane["to"];
                        break;
                    }
                }
                if ($isReached) {
                    $ans = [$target, $previousStar[1] + 1, $previousStar[0]];
                    break;
                } else {
                    $previousStar = array_shift($queue);
                    $hyperLanes = Star::where("id", $previousStar[0])->first()->hyperlane;
                    $hyperLanes = json_decode($hyperLanes, true);
                }
            }
            unset($hyperLanes);
            unset($queue);
            $route = [$target,];
            $prev = $ans[2];
            while (true) {
                foreach ($routeFinder as $item) {
                    if ($prev == 0) {
                        break;
                    } elseif ($prev == $item[0]) {
                        $route[] = $item[0];
                        $prev = $item[1];
                    }
                }
                if ($prev == 0) {
                    break;
                }
            }
            $route = array_reverse($route);
            Army::where('id',$id)->update(["moving"=>$route]);
        }
    }
    public function deleteArmy(Request $request) {
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        $army = Fleet::where(["id"=>$request->input('id')])->first()->toArray();
        if (($privilege == 2 && $army['owner'] == $country) || $privilege <= 1) {
            Army::where(["id"=>$army['id']])->delete();
        }
    }
    public function newFleet(Request $request) {
        $uid = $request->session()->get('uid');
        $country = $request->input('country');
        $name = $request->input('name');
        $weaponA = $request->input('weaponA');
        $weaponB = $request->input('weaponB');
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $capital = Country::where(["tag"=>$country])->first()->capital;
        $privilege = $MadokaUser->privilege;
        if (($MadokaUser->country == $country && $privilege == 2) || $privilege <= 1) {
            $fleet = newFleet();
            $fleet->owner = $country;
            $fleet->name = $name;
            $fleet->ships = '[]';
            $fleet->position = $capital;
            $fleet->ftl = 0;
            $fleet->weaponA = $weaponA;
            $fleet->weaponB = $weaponB;
            $fleet->computer = 2;
            $fleet->save();
        }
    }
}
