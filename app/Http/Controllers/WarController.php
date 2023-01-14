<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Definition;
use App\Models\Fleet;
use app\Models\Ship;
use App\Models\ShipComputer;
use Illuminate\Support\Facades\DB;

class WarController extends Controller{

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
                    if ($dataArr[$key][$key2]['computer'] == 'A' && $dataArr[$key][$key2]['hull'] < 0.2 * $dataArr[$key][$key2]['fullHull']) {
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
}
