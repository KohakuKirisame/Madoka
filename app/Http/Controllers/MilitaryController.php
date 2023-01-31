<?php

namespace App\Http\Controllers;

use App\Models\Army;
use App\Models\Country;
use App\Models\Definition;
use App\Models\Fleet;
use App\Models\Planet;
use App\Models\Ship;
use App\Models\ShipComputer;
use App\Models\ShipType;
use App\Models\Star;
use Illuminate\Http\Request;
use App\Models\User;

class MilitaryController extends Controller{

    public function spaceBattle($fleets) {
        $position = Fleet::where(["id"=>$fleets[0]])->first()->position;
        $starType = Star::where(["id"=>$position])->first()->type;
        $flags = array();
        foreach ($fleets as $key => $value) {
            $owner = Fleet::where(["id" => $value])->first()->owner;
            if (in_array($owner, $flags)) {
                continue;
            } else {
                array_push($flags, $owner);
            }
        }
        for ($i = 0; $i < count($fleets); $i++) {
            $fleets[$i] = [$fleets[$i],];
            for ($j = $i + 1; $j < count($fleets); $j++) {
                $id1 = $fleets[$i][0];
                $owner1 = Fleet::where(["id" => $id1])->first()->owner;
                $id2 = $fleets[$j];
                $owner2 = Fleet::where(["id" => $id2])->first()->owner;
                $ally = json_decode(Country::where(["tag" => $owner1])->first()->alliedWith,true);
                if (in_array($owner2, $ally)) {
                    array_push($fleets[$i], $fleets[$j]);
                    array_splice($fleets,$j,1);
                }
            }
        }
        $dataArr = array();
        foreach ($fleets as $key => $value) {
            $dataArr[$key] = [];
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
                if ($starType == 'sc_pulsar') {
                    $shield = 0;
                } elseif ($starType == 'sc_neutron_star') {
                    $evasion *=0.5;
                    $speed *=0.5;
                } elseif ($starType == 'sc_black_hole') {
                    $disengageChance *= 0.5;
                }
                //////
                $dataArr[$key][] = ["hull" => $hull,
                    "fullHull" => $hull,
                    "PDamage" => $PDamage,
                    "EDamage" => $EDamage,
                    "shield" => $shield,
                    "armor" => $armor,
                    "evasion" => $evasion,
                    "speed" => $speed,
                    "ships" => $ships,
                    "disengageChance" => $disengageChance,
                    "computer" => $computer,
                    "owner"=>$data->owner,
                    "shipComputerModifier"=>0];
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
            $computers[$key][1] = json_decode($value[1], true);
        }
//////////
        while (count($dataArr) > 1) {
            $damageBullet = array();
            foreach ($dataArr as $allyKey => $fleetsInBattle) {
                $damageBullet[$allyKey] = array();
                foreach ($fleetsInBattle as $fleetKey => $fleetData) {
                    if ($fleetData['computer'] == 1 && $fleetData['hull'] < 0.2 * $fleetData['fullHull']) {
                        $dataArr[$allyKey][$fleetKey]['hull'] = 0;
                        $damageBullet[$allyKey][] = [
                            'PDamage' => $fleetData['PDamage'] * 2,
                            'EDamage' => $fleetData['EDamage'],
                            'computer' => $fleetData['computer'],
                            'owner' => $fleetData['owner'],
                            'shipComputerModifier' => 0];
                    } else {
                        $damageBullet[$allyKey][] = [
                            'PDamage' => $fleetData['PDamage'],
                            'EDamage' => $fleetData['EDamage'],
                            'computer' => $fleetData['computer'],
                            'owner' => $fleetData['owner'],
                            'shipComputerModifier' => 0];
                    }
                }
            }
            foreach ($dataArr as $allyKey => $fleetsInBattle) {// 循环国
                $enemyShips = 0;
                foreach ($dataArr as $allyKey2 => $fleetsInBattle2) {
                    if ($allyKey2 == $allyKey) {
                        continue;
                    } else {
                        foreach ($fleetsInBattle2 as $fleetKey => $fleetData) {
                            $enemyShips += $fleetData['ships'];
                        }
                    }
                }
                foreach ($dataArr as $allyKey2 => $fleetsInBattle2) {// 循环除我之外的舰队
                    if ($allyKey2 == $allyKey) {
                        continue;
                    }
                    foreach ($fleetsInBattle2 as $enemyFleetKey => $enemyFleetData) {// 敌人联合内部
                        foreach ($damageBullet as $bulletAlly => $bullets) {// 子弹国家
                            if ($bulletAlly == $allyKey2) {
                                continue;
                            }
                            foreach ($bullets as $bulletKey => $bullet) {// 子弹内部
                                for ($i = 0; $i < count($computerModifier); $i++) {
                                    if ($bullet['owner'] == $computerModifier[$i][0]) {
                                        $damageBullet[$bulletAlly][$bulletKey]['shipComputerModifier'] = $computerModifier[$i][1];
                                    }
                                }
                                for ($i = 0; $i < count($computerModifier); $i++) {
                                    if ($enemyFleetData['owner'] == $computerModifier[$i][0]) {
                                        $dataArr[$allyKey2][$enemyFleetKey]['shipComputerModifier'] = $computerModifier[$i][1];
                                    }
                                }
                                ///////////
                                for ($i = 0; $i < 4; $i++) {
                                    if ($damageBullet[$bulletAlly][$bulletKey]['computer'] == $computers[$i][0]) {
                                        if ($computers[$i][2] == $dataArr[$allyKey2][$enemyFleetKey]['computer']) {
                                            $dataArr[$allyKey2][$enemyFleetKey]['shipComputerModifier'] *= (1 - 0.1 * $damageBullet[$bulletAlly][$bulletKey]['shipComputerModifier']);
                                        }
                                    }
                                }
                                $bulletModifier = array();
                                for ($i = 0; $i < 4; $i++) {
                                    if ($damageBullet[$bulletAlly][$bulletKey]['computer'] == $computers[$i][0]) {
                                        $bulletModifier = $computers[$i][1];
                                    }
                                }
                                foreach ($bulletModifier as $modifierName => $modifierValue) {// 子弹修正
                                    $modifierKey = Definition::where(['name' => $modifierName])->first()->modifierKey;//$conn->query("SELECT modifierKey FROM Definitions WHERE name='$key6'")
                                    if ($modifierKey == 'PDamage') {
                                        $damageBullet[$bulletAlly][$bulletKey]['PDamage'] *= 1 + $modifierValue + $damageBullet[$bulletAlly][$bulletKey]['shipComputerModifier'];
                                    } elseif ($modifierKey == 'EDamage') {
                                        $damageBullet[$bulletAlly][$bulletKey]['EDamage'] *= 1 + $modifierValue + $damageBullet[$bulletAlly][$bulletKey]['shipComputerModifier'];
                                    }
                                }
                                $shipModifier = [];
                                for ($i = 0; $i < 4; $i++) {
                                    if ($dataArr[$allyKey2][$enemyFleetKey]['computer'] == $computers[$i][0]) {
                                        $shipModifier = $computers[$i][1];
                                    }
                                }
                                foreach ($shipModifier as $modifierName => $modifierValue) {// 受伤国加成
                                    $modifierKey = Definition::where(['name' => $modifierName])->first()->modifierKey;
                                    $dataArr[$allyKey2][$enemyFleetKey][$modifierKey] *= 1 + $modifierValue + $dataArr[$allyKey2][$enemyFleetKey]['shipComputerModifier'];
                                }
                                ///////开打////////
                                $PDamage = $damageBullet[$bulletAlly][$bulletKey]['PDamage'];
                                $EDamage = $damageBullet[$bulletAlly][$bulletKey]['EDamage'];
                                if ($dataArr[$allyKey2][$enemyFleetKey]['shield'] > 0) {
                                    $dataArr[$allyKey2][$enemyFleetKey]['shield'] -= ($PDamage * 0.5 + $EDamage * 1.5) * (1 - $dataArr[$allyKey2][$enemyFleetKey]['evasion']) * $dataArr[$allyKey2][$enemyFleetKey]['ships'] / $enemyShips;
                                    echo $fleets[$allyKey2][$enemyFleetKey], '|', $dataArr[$allyKey2][$enemyFleetKey]['shield'], '|', $dataArr[$allyKey2][$enemyFleetKey]['armor'], '|', $dataArr[$allyKey2][$enemyFleetKey]['hull'], "<br>";
                                } else {
                                    if ($dataArr[$allyKey2][$enemyFleetKey]['armor'] > 0) {
                                        $dataArr[$allyKey2][$enemyFleetKey]['armor'] -= ($PDamage * 1.5 + $EDamage * 0.5) * (1 - $dataArr[$allyKey2][$enemyFleetKey]['evasion']) * $dataArr[$allyKey2][$enemyFleetKey]['ships'] / $enemyShips;
                                        echo $fleets[$allyKey2][$enemyFleetKey], '|', $dataArr[$allyKey2][$enemyFleetKey]['shield'], '|', $dataArr[$allyKey2][$enemyFleetKey]['armor'], '|', $dataArr[$allyKey2][$enemyFleetKey]['hull'], "<br>";
                                    } else {
                                        if ($dataArr[$allyKey2][$enemyFleetKey]['hull'] > 0) {
                                            if ($dataArr[$allyKey2][$enemyFleetKey]['hull'] <= 0.5 * $dataArr[$allyKey2][$enemyFleetKey]['fullHull']) {
                                                $damage = ($PDamage * 1 + $EDamage * 1) * (1 - $dataArr[$allyKey2][$enemyFleetKey]['evasion']) * $dataArr[$allyKey2][$enemyFleetKey]['ships'] / $enemyShips;
                                                $disengage = ($damage / $dataArr[$allyKey2][$enemyFleetKey]['hull'] * 1.5 * $dataArr[$allyKey2][$enemyFleetKey]['disengageChance']);
                                                echo '~', $disengage, '~';
                                                echo $fleets[$allyKey2][$enemyFleetKey], '|', $dataArr[$allyKey2][$enemyFleetKey]['shield'], '|', $dataArr[$allyKey2][$enemyFleetKey]['armor'], '|', $dataArr[$allyKey2][$enemyFleetKey]['hull'], "<br>";
                                                if ($disengage > 1) {
                                                    array_splice($dataArr[$allyKey2],$enemyFleetKey,1);
                                                    if (count($dataArr[$allyKey2]) == 0) {
                                                        array_splice($dataArr,$allyKey2,1);
                                                    }
                                                    ///撤退///
                                                    $owner = Fleet::where(["id"=>$fleets[$allyKey2][$enemyFleetKey]])->first()->owner;
                                                    $cap = Country::where(["tag" => $owner])->first()->capital;//$conn->query("SELECT capital FROM Country WHERE id='$owner'")
                                                    $result = Fleet::where(["id" =>$fleets[$allyKey2][$enemyFleetKey]])->update(["position" => $cap]);//$conn->query("UPDATE Fleet SET position=$cap WHERE id=$id");
                                                    echo "!".$fleets[$allyKey2][$enemyFleetKey]."!";
                                                    break 4;
                                                } else {
                                                    $random = random_int(1, 100);
                                                    if ($random <= 100 * $disengage) {
                                                        array_splice($dataArr[$allyKey2],$enemyFleetKey,1);
                                                        if (count($dataArr[$allyKey2]) == 0) {
                                                            array_splice($dataArr,$allyKey2,1);
                                                        }
                                                        ///撤退///
                                                        $owner = Fleet::where(["id"=>$fleets[$allyKey2][$enemyFleetKey]])->first()->owner;
                                                        $cap = Country::where(["tag" => $owner])->first()->capital;//$conn->query("SELECT capital FROM Country WHERE id='$owner'")
                                                        $result = Fleet::where(["id" =>$fleets[$allyKey2][$enemyFleetKey]])->update(["position" => $cap]);//$conn->query("UPDATE Fleet SET position=$cap WHERE id=$id");
                                                        echo "!".$fleets[$allyKey2][$enemyFleetKey]."!";
                                                        break 4;
                                                    } else {
                                                        $dataArr[$allyKey2][$enemyFleetKey]['hull'] -= ($PDamage * 1 + $EDamage * 1) * (1 - $dataArr[$allyKey2][$enemyFleetKey]['evasion']) * $dataArr[$allyKey2][$enemyFleetKey]['ships'] / $enemyShips;
                                                        echo $fleets[$allyKey2][$enemyFleetKey], '|', $dataArr[$allyKey2][$enemyFleetKey]['shield'], '|', $dataArr[$allyKey2][$enemyFleetKey]['armor'], '|', $dataArr[$allyKey2][$enemyFleetKey]['hull'], "<br>";
                                                    }
                                                }
                                            } else {
                                                $dataArr[$allyKey2][$enemyFleetKey]['hull'] -= ($PDamage * 1 + $EDamage * 1) * (1 - $dataArr[$allyKey2][$enemyFleetKey]['evasion']) * $dataArr[$allyKey2][$enemyFleetKey]['ships'] / $enemyShips;
                                                echo $fleets[$allyKey2][$enemyFleetKey], '|', $dataArr[$allyKey2][$enemyFleetKey]['shield'], '|', $dataArr[$allyKey2][$enemyFleetKey]['armor'], '|', $dataArr[$allyKey2][$enemyFleetKey]['hull'], "<br>";
                                            }
                                        } else {
                                            array_splice($dataArr[$allyKey2],$enemyFleetKey,1);
                                            if (count($dataArr[$allyKey2]) == 0) {
                                                array_splice($dataArr,$allyKey2,1);
                                            }
                                            ///撤退///
                                            $owner = Fleet::where(["id"=>$fleets[$allyKey2][$enemyFleetKey]])->first()->owner;
                                            $cap = Country::where(["tag" => $owner])->first()->capital;//$conn->query("SELECT capital FROM Country WHERE id='$owner'")
                                            $result = Fleet::where(["id" =>$fleets[$allyKey2][$enemyFleetKey]])->update(["position" => $cap]);//$conn->query("UPDATE Fleet SET position=$cap WHERE id=$id");
                                            echo "!".$fleets[$allyKey2][$enemyFleetKey]."!";
                                            break 4;
                                        }
                                    }
                                }
                                /////
                                $shipModifier = [];
                                for ($i = 0; $i < 4; $i++) {
                                    if ($dataArr[$allyKey2][$enemyFleetKey]['computer'] == $computers[$i][0]) {
                                        $shipModifier = $computers[$i][1];
                                    }
                                }
                                foreach ($shipModifier as $modifierName => $modifierValue) {// 受伤国加成
                                    $modifierKey = Definition::where(['name' => $modifierName])->first()->modifierKey;
                                    $dataArr[$allyKey2][$enemyFleetKey][$modifierKey] /= 1 + $modifierValue + $dataArr[$allyKey2][$enemyFleetKey]['shipComputerModifier'];
                                }
                                /////
                            }
                        }
                    }
                }
            }
        }
        foreach ($dataArr as $allyKey => $fleetInBattle) {
            foreach ($fleetInBattle as $fleetKey => $fleetData) {
                while ($fleetData['ships'] > 1) {
                    foreach ($fleets[$allyKey] as $fleetAllyKey => $fleetAllyValue) {
                        $f = Fleet::where(['id' => $fleetAllyValue])->first();
                        $ships = json_decode($f->ships, true);
                        while (count($ships) > 1) {
                            $shipKey = array_rand($ships, 1);
                            $shipID = $ships[$shipKey];
                            array_splice($ships,$shipKey,1);
                            $f->ships = json_encode($ships, JSON_UNESCAPED_UNICODE);
                            $f->save();
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
        while ($army1['HP']>0|| $army2['HP']>0) {
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
        $fleet = Fleet::where(["id" => $id])->first();
        $ships = json_decode($fleet->ships,true);
        $hull = $PDamage = $EDamage = $shield = $armor = $evasion = $speed = $commandPoints = $disengageChance = 0;
        foreach ($ships as $key => $value) {
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
        $evasion = $evasion/($commandPoints+1);
        $speed = $speed/($commandPoints+1);
        $disengageChance = $disengageChance/($commandPoints+1);

        $fleet->owner = $fleet['owner'];
        $fleet->hull = $hull;
        $fleet->PDamage = $PDamage;
        $fleet->EDamage = $EDamage;
        $fleet->shield = $shield;
        $fleet->armor = $armor;
        $fleet->evasion = $evasion;
        $fleet->speed = $speed;
        $fleet->disengageChance =$disengageChance;
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
        $fleet->EDamage *= $weapon1;
        $fleet->armor *= 1+($weapon1*0.1);
        $fleet->PDamage *= $weapon2;
        $fleet->shield *= 1+($weapon2*0.1);
        $data = Country::where(["tag" => $fleet->owner])->first()->toArray();
        $fleet->hull *= 1+$data['shipHullModifier'];
        $fleet->PDamage *= 1+$data['shipPDamageModifier'];
        $fleet->EDamage *= 1+$data['shipEDamageModifier'];
        $fleet->shield *= 1+$data['shipShieldModifier'];
        $fleet->armor *= 1+$data['shipArmorModifier'];
        $fleet->evasion *= 1+$data['shipEvasionModifier'];
        $fleet->speed *= 1+$data['shipSpeedModifier'];
        $fleet->disengageChance *=1+$data['shipDisengageChanceModifier'];

        $fleet->power = ((0.25*($fleet->hull+$fleet->shield+$fleet->armor)/(1-$fleet->evasion)*
                        0.25*($fleet->PDamage+$fleet->EDamage))^0.25)*0.005;
        $fleet->save();
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
            $techs = json_decode(Country::where(["tag"=>$country])->first()->techs,true);
            $fleets = Fleet::where(["owner"=>$country])->get()->toArray();
            $ftls = ["超空间引擎",];
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
        foreach ($armys as $key => $army) {
            $army['position'] = Star::where(["id"=>$army['position']])->first()->name;
            $armys[$key] = $army;
        }
        $computers = ShipComputer::get()->toArray();
        $shipTypes = ShipType::get()->toArray();
        if ($privilege == 2) {
            foreach ($shipTypes as $key => $value) {
                if (!in_array($value['name'], $techs)) {
                    unset($shipTypes[$key]);
                }
            }
        }
        return view('military', ['user' => $user,"privilege"=>$privilege,"country"=>$country,
            'fleets'=>$fleets,"ftls"=>$ftls,"computers"=>$computers,"shipTypes"=>$shipTypes,
            'armys'=>$armys]);
    }
    public function readFleet(Request $request) {
        $id = $request->input('id');
        $fleet = new MilitaryController();
        $fleet->fleetCount($id);
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
                "shipList"=>$shipList,"weaponA"=>$fleet->weaponA,"weaponB"=>$fleet->weaponB,"power"=>$fleet->power];
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
    public function newShip(Request $request) {
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
            $fleet->ships = json_encode($ships,JSON_UNESCAPED_UNICODE);
            $fleet->save();
        } elseif ($privilege == 2) {
            $country = Country::where(["tag"=>$fleet->owner])->first();
            $shipType = ShipType::where(["type"=>$type])->first();

            $ship = new Ship();
            $ship->name = $type;
            $ship->owner = $fleet->owner;
            $ship->shipType = $type;
            $ship->save();
            $ships = json_decode($fleet->ships,true);
            $ships[] = $ship->id;
            $fleet->ships = json_encode($ships,JSON_UNESCAPED_UNICODE);
            $fleet->save();
        }
        $this->fleetCount($id);
    }
    public function getFleets(Request $request) {
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        if ($privilege <= 1) {
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
        if ($request->input('f1') == $request->input('f2')) {
            return;
        }
        if (($privilege == 2 && $fleet1['owner'] == $country && $fleet2['owner'] == $country &&$fleet1['id']!=$fleet2['id']) || $privilege <= 1) {
            $fleet1['ships'] = json_decode($fleet1['ships'], true);
            $fleet2['ships'] = json_decode($fleet2['ships'], true);
            if (in_array($ship,$fleet1['ships'])) {
                $fleet2['ships'][] = $ship;
                foreach($fleet1['ships'] as $key => $value) {
                    if ($ship == $value) {
                        array_splice($fleet1['ships'],$key,1);
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
    function moveBFS($start,$targetStar) {
        $hyperLanes = Star::where("id", $start)->first()->hyperlane;
        $hyperLanes = json_decode($hyperLanes, true);
        $queue = [];
        $previousStar = [$start, 0, 0];
        $routeFinder = [[$start, 0],];
        $visited = [$start,];
        $isReached = false;
        $i = 0;
        while ($i<400) {
            foreach ($hyperLanes as $hyperLane) {
                if (!in_array($hyperLane["to"],$visited)) {
                    $queue[] = [$hyperLane["to"], $previousStar[1] + 1, $previousStar[0]];
                    $routeFinder[] = [$hyperLane["to"], $previousStar[0]];
                    $visited[] = $hyperLane["to"];
                }
                if ($hyperLane["to"] == $targetStar) {
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
            $i++;
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
        return $route = array_reverse($route);
    }
    function moveCount($type,$id) {
        if ($type == 'army') {
            $army = Army::where("id", $id)->first();
            $moving = json_decode($army->moving, true);
            if (count($moving) == 0) {
                return;
            } else {
                $starNow = Star::where("id", $army->position)->first();
                $country = Country::where("tag", $army->owner)->first();
                $war = json_decode($country->atWarWith,true);
                if (in_array($starNow->controller,$war)) {
                    $army->moving = '[]';
                    return;
                } else {
                    $army->position = array_shift($moving);
                    $army->moving = json_encode($moving,JSON_UNESCAPED_UNICODE);
/*                    if ($starNow->controller == $army->owner && $starNow->havePlanet == 1) {
//                        echo $army->position;
//                        $planet = Planet::where(["position"=>$army->position])->first();
//                        if (!is_null($planet) && $planet->controller != $army->controller) {
//                            $enemy = Army::where(["position"=>$army->position])->first();
//                            if (!is_null($enemy)) {
////                                $this->armyBattle($army->id,$enemy->id);
//                                $enemy = Army::where(["position"=>$army->position])->first();
//                                if (is_null($enemy)) {
//                                    $planet->controller = $army->owner;
//                                    $planet->save();
//                                }
//                            }
//                        }
//                    }*/
                    $army->save();
                }
            }
            $army->save();
        } elseif ($type == 'fleet') {
            $fleet = Fleet::where("id", $id)->first();
            $moving = json_decode($fleet->moving, true);
            if (count($moving) == 0) {
                return;
            } else {
                $jump = $fleet->speed/100;
                while ($jump > 0 && count($moving) > 0) {
                    $starController = Star::where(["id"=>$fleet->position])->first()->controller;
                    $country = Country::where("tag", $fleet->owner)->first();
                    $war = json_decode($country->atWarWith,true);
                    if (in_array($starController,$war)) {
                        $fleets = Fleet::where(["position"=>$fleet->position])->get()->toArray();
                        $fleetInThis = [$id,];
                        foreach ($fleets as $item) {
                            $fleetInThis[] = $item['id'];
                        }
                        $this->spaceBattle($fleetInThis);
                        $fleet = Fleet::where("id", $id)->first();
                        $starController = Star::where("id", $fleet->position)->first()->controller;
                        $country = Country::where("tag", $fleet->owner)->first();
                        $war = json_decode($country->atWarWith,true);
                        if (in_array($starController,$war)) {
                            Star::where(["id"=>$fleet->position])->update(["controller"=>$fleet->owner]);
                            $fleet->position = array_shift($moving);
                        }
                    } else {
//                    var_dump($moving);
                        $fleet->position = array_shift($moving);
                        if(count($moving) == 0) {
                        break;
                        }
                    }
                    $jump -= 1;
                }
                $fleet->moving = json_encode($moving,JSON_UNESCAPED_UNICODE);
                $fleet->save();
            }
        }
    }
    public function deleteArmy(Request $request) {
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        $army = Army::where(["id"=>$request->input('id')])->first()->toArray();
        if (($privilege == 2 && $army['owner'] == $country) || $privilege <= 1) {
            Army::where(["id"=>$army['id']])->delete();
        }
    }
    public function newFleet(Request $request) {
        $uid = $request->session()->get('uid');
        $country = $request->input('owner');
        $name = $request->input('name');
        $weaponA = $request->input('weaponA');
        $weaponB = $request->input('weaponB');
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        if (!is_null($country) || $country!='') {
            echo $country;
            $capital = Country::where(["tag" => $country])->first()->capital;
        } else {
            $capital = 297;
            $country = 'GSK';
        }
        echo $capital;
        if (($MadokaUser->country == $country && $privilege == 2) || $privilege <= 1) {
            echo 1;
            $fleet = new Fleet();
            $fleet->owner = $country;
            $fleet->name = $name;
            $fleet->ships = '[]';
            $fleet->moving = '[]';
            $fleet->position = $capital;
            $fleet->ftl = 0;
            $fleet->weaponA = $weaponA;
            $fleet->weaponB = $weaponB;
            $fleet->computer = 2;
            $fleet->save();
        }
    }
    public function move(Request $request) {
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $type = $request->input('type');
        $id = $request->input('id');
        $target = $request->input('target');
        $targetID = Star::where(["name"=>$target])->first()->id;
        if ($type == 'fleet') {
            $fleet = Fleet::where(["id"=>$id])->first();
            if (($MadokaUser->country == $fleet->owner && $privilege == 2) || $privilege <= 1) {
                $route = $this->moveBFS($fleet->position,$targetID);
                $route = json_encode($route,JSON_UNESCAPED_UNICODE);
                $fleet->moving = $route;
                $fleet->save();
            }
        } elseif($type == 'army') {
            $army = Army::where(["id"=>$id])->first();
            if (($MadokaUser->country == $army->owner && $privilege == 2) || $privilege <= 1) {
                $route = $this->moveBFS($army->position,$targetID);
                $route = json_encode($route,JSON_UNESCAPED_UNICODE);
                $army->moving = $route;
                $army->save();
            }
        }

    }
}
//$mili = new MilitaryController();
//$mili->spaceBattle([8,18,19]);
