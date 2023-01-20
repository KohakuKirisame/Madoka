<?php

namespace app\Http\Controllers;
use App\Models\Country;
use App\Models\Definition;
use app\Models\Planet;
use app\Models\PlanetType;
use app\Models\Population;
use app\models\District;
use app\models\Job;

class PlanetController extends Controller {

    public int $id,$position,$size;
    public string $name,$type,$owner,$controller,$leaderParty,$popGrowthProcess;
    public array $pops;
    public array $districts;
    public array $product;

    function __construct($id) {
        $p =Planet::where(["id"=>$id])->first();
        $this->id = $id;
        $this->name = $p ->name;
        $this->position = $p->position;
        $this->type = $p->type;
        $this->size = $p->size;
        $this->owner = $p->owner;
        $this->controller = $p->controller;
        $this->pops = json_decode($p->pops,true);
        $this->popGrowthProcess = $p->popGrowthProcess;
        $this->districts = json_decode($p->districts,true);
        $this->product = json_decode($p->product,true);
        $this->leaderParty = $p->leaderParty;
    }
    function updatePlanet() {
        $pops = json_encode($this->pops,JSON_UNESCAPED_UNICODE);
        $districts = json_encode($this->districts,JSON_UNESCAPED_UNICODE);
        $product = json_encode($this->product,JSON_UNESCAPED_UNICODE);
        Planet::where(["id"=>$this->id])->update(["position"=>$this->position,"name"=>$this->name,"size"=>$this->size,"controller"=>$this->controller,"pops"=>$pops,"popGrowthProcess"=>$this->popGrowthProcess,
                                            "districts"=>$districts,"product"=>$product,"leaderParty"=>$this->leaderParty]);
    }
    //资源计算//
    function countRes() {
        foreach ($this->product as $key => $value) {
            $this->product[$key] = 0;
        }
        foreach ($this->districts as $key => $value) {
            foreach ($value['product'] as $key2 => $value2) {
                $this->product[$key2] += $value2;
            }
        }
    }
    //主权变更//
    function colonize($owner) {
        Planet::where(["id"=>$this->id])->update(["owner"=>$owner,"controller"=>$owner]);
        echo "殖民地信息已更新\n";
    }
    function beOccupied($controller) {
        Planet::where(["id"=>$this->id])->update(["controller"=>$controller]);
        echo "殖民地信息已更新\n";
    }
    //区划计算//
    function districtCount() {
        $market = new MarketController($this->controller);
        $result = Country::where(["id" => $this->owner]) - first();
        $dTax = $result->districtTax;
        $upTax = $result->upTax;
        $midTax = $result->midTax;
        $lowTax = $result->lowTax;
        $result = Job::get();
        $jobData = array();
        foreach ($result as $d) {
            $jobData[] = $d;
        }
        foreach ($this->districts as $key => $value) {
            $disData = District::where(["name" => $key])->first;
            if ($key == '行政区划') {
                if ($value['size'] != floor(count($this->pops) / 10)) {
                    $value['size'] = floor(count($this->pops) / 10);
                }
                $value['cashFlag'] = 1;
                foreach ($value['jobs'] as $key2 => $value2) {
                    if ($key2 == 'upJob') {
                        $this->product['energy'] -= 2 * $market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['upJob']);
                        foreach ($value2 as $key3 => $value3) {
                            $cash = Population::where(["id" => $value3])->first()->cash;
                            $cash += 2 * $market->goods['consume_goods']['price'];
                            $cash -= $upTax * 2 * $market->goods['consume_goods']['price'];
                            Population::where(["id" => $value3])->update("cash", $cash);
                        }
                    } elseif ($key2 == 'midJob') {
                        $this->product['energy'] -= 1.5 * count($this->districts[$key]['jobs']['midJob']);
                        foreach ($value2 as $key3 => $value3) {
                            $cash = Population::where(["id" => $value3])->first()->cash;
                            $cash += 1.5 * $market->goods['consume_goods']['price'];
                            $cash -= $midTax * 1.5 * $market->goods['consume_goods']['price'];
                            Population::where(["id" => $value3])->update("cash", $cash);
                        }
                    } else {
                        $this->product['energy'] -= 1 * count($this->districts[$key]['jobs']['upJob']);
                        foreach ($value2 as $key3 => $value3) {
                            $cash = Population::where(["id" => $value3])->first()->cash;
                            $cash += 1 * $market->goods['consume_goods']['price'];
                            $cash -= $lowTax * 1 * $market->goods['consume_goods']['price'];
                            Population::where(["id" => $value3])->update("cash", $cash);
                        }
                    }
                }
                continue;
            }
            foreach ($value as $key2 => $value2) {
                $popJob = Population::where(["id" => $value2])->first()->job;
                foreach ($jobData as $value3) {
                    if ($value3->name == $popJob) {
                        $demand = json_decode($value3->demand, true);
                        $supply = json_decode($value3->supply, true);
                        foreach ($demand as $goods => $value4) {
                            $modifierName = Definition::where(["area" => "economy", "economyKey" => "consume", "modifierKey" => $key2])->first()->modifierName;
                            $modifier = 1 + Country::where(["id" => $this->owner])->first()->$modifierName;
                            $this->districts[$key]['product'][$goods] -= $value4 * $modifier;
                        }
                        foreach ($supply as $goods => $value4) {
                            $modifierName = Definition::where(["area" => "economy", "economyKey" => "produce", "modifierKey" => $key2])->first()->modifierName;
                            $modifier = 1 + Country::where(["id" => $this->owner])->first()->$modifierName;
                            $this->districts[$key]['product'][$goods] += $value4 * $modifier;
                        }
                    }
                }
                $cash0 = $this->districts[$key]['cash'];
                if ($this->districts[$key]['cash'] <= -500) {
                    if ($this->districts[$key]['size'] < 2) {
                        $this->districts[$key]['size'] = 0;
                        foreach ($this->districts[$key]['jobs'] as $key2 => $value2) {
                            foreach ($value2 as $key3 => $value3) {
                                Population::where(["id" => $value])->update(["job" => "无", "workat" => "无"]);
                            }
                        }
                    } else {
                        $this->districts[$key]['size'] -= 1;
                    }
                } else {
                    foreach ($this->districts[$key]['product'] as $key2 => $value2) {
                        if ($value2 > 0) {
                            $this->districts[$key]['cash'] += $value2 * $market->goods[$key2]['price'];
                        } else {
                            $this->districts[$key]['cash'] -= $value2 * $market->goods[$key2]['price'];
                        }
                    }
                    foreach ($this->districts[$key]['jobs'] as $key2 => $value2) {
                        if ($key2 == 'upJob') {
                            $this->districts[$key]['cash'] -= 2 * $market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['upJob']);
                            foreach ($value2 as $key3 => $value3) {
                                $cash = Population::where(["id" => $value3])->first()->cash;
                                $salary = 2 * $market->goods['consume_goods']['price'];
                                $cash += (1 - $upTax) * $salary;
                                $this->product['energy'] += $upTax * $salary;
                                Population::where(["id" => $value3])->update(["cash" => $cash]);
                            }
                        } elseif ($key2 == 'midJob') {
                            if ($this->districts[$key]['cash'] - $cash0 < 0) {
                                $this->districts[$key]['cash'] -= 1 * $market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['upJob']);
                                foreach ($value2 as $key3 => $value3) {
                                    $cash = Population::where(["id" => $value3])->first()->cash;
                                    $salary = 1 * $market->goods['consume_goods']['price'];
                                    $cash += (1 - $midTax) * $salary;
                                    $this->product['energy'] += $upTax * $salary;
                                    Population::where(["id" => $value3])->update(["cash" => $cash]);
                                }
                            } else {
                                $this->districts[$key]['cash'] -= 1.5 * count($this->districts[$key]['jobs']['midJob']);
                                foreach ($value2 as $key3 => $value3) {
                                    $cash = Population::where(["id" => $value3])->first()->cash;
                                    $salary = 1.5 * $market->goods['consume_goods']['price'];
                                    $cash += (1 - $midTax) * $salary;
                                    $this->product['energy'] += $upTax * $salary;
                                    Population::where(["id" => $value3])->update(["cash" => $cash]);
                                }
                            }
                        } else {
                            if ($this->districts[$key]['cash'] - $cash0 < 0) {
                                $this->districts[$key]['cash'] -= 0.4 * $market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['lowJob']);
                                foreach ($value2 as $key3 => $value3) {
                                    $cash = Population::where(["id" => $value3])->first()->cash;
                                    $salary = 0.4 * $market->goods['consume_goods']['price'];
                                    $cash += (1 - $lowTax) * $salary;
                                    $this->product['energy'] += $upTax * $salary;
                                    Population::where(["id" => $value3])->update(["cash" => $cash]);
                                }
                            } else {
                                $this->districts[$key]['cash'] -= 1 * count($this->districts[$key]['jobs']['upJob']);
                                foreach ($value2 as $key3 => $value3) {
                                    $cash = Population::where(["id" => $value3])->first()->cash;
                                    $salary = 1 * $market->goods['consume_goods']['price'];
                                    $cash += (1 - $lowTax) * $salary;
                                    $this->product['energy'] += $upTax * $salary;
                                    Population::where(["id" => $value3])->update(["cash" => $cash]);
                                }
                            }
                        }
                    }
                    if ($this->districts[$key]['cash'] - $cash0 < 0) {
                        $this->districts[$key]['profit'] = $this->districts[$key]['cash'] - $cash0;
                        continue;
                    } else {
                        $this->districts[$key]['profit'] = $this->districts[$key]['cash'] - $cash0;
                        $tax = $dTax * ($this->districts[$key]['cash'] - $cash0);
                        $this->districts[$key]['cash'] -= $tax;
                        $this->product['energy'] += $tax;
                    }
                    //////////
                    $sizeAll = 0;
                    foreach ($this->districts as $key2 => $value2) {
                        $sizeAll += $value2['size'];
                    }
                    if ($sizeAll += 1 < $this->size && $this->districts[$key]['cash'] > 2 * $disData->buildCost) {
                        $this->districts[$key]['size'] += 1;
                        $this->districts[$key]['cash'] -= $this->districts[$key]['cash'] - $disData->buildCost;
                    }
                    ///////////
                    if ($this->districts[$key]['cash'] < $disData->buildCost && $this->districts[$key]['size'] > 1 && floor($this->districts[$key]['size']) != 1) {
                        $this->districts[$key]['size'] -= 1;
                        $this->districts[$key]['cash'] += $disData->buildCost;
                    }
                }
            }
            if ($this->product['energy'] < 0) {
                $energyProduce = -$this->product['energy'];
                $modifier = Country::where(["id" => $this->owner])->first()->energuProduceModifier;
                $this->product['energy'] = $energyProduce * $modifier;
            }
            $this->countRes();
            $this->updatePlanet();
        }
    }
    //人口增长//
    function popGrowth() {
        $typeD = PlanetType::where(["name"=>$this->type])->first;
        $carryAble = $typeD->carryAble * $this->size;
        $growth = 0;
        if ($carryAble > 2*count($this->pops)) {
            $growth = 3*max(0.125*(count($this->pops)-(count($this->pops)*count($this->pops)/$carryAble)-1),1);
        }
        elseif ($carryAble <= 2*count($this->pops)) {
            $growth = 3*0.125*(count($this->pops)-(count($this->pops)*count($this->pops)/$carryAble)-1);
        }
        if ($this->product['consume_goods'] < 0) {
            $growth *= 1/sqrt(abs($this->product['consume_goods']));
        }
        elseif ($this->product['consume_goods'] > 0) {
            $growth *= sqrt($this->product['consume_goods']);
        }
        $this->popGrowthProcess += $growth;
        if ($this->popGrowthProcess >= 100) {
            $result = Population::get();//$conn->query("SELECT * FROM Pops");
            $pops = [];
            foreach ($result as $item) {
                $pops[] = $item;
            }
            $id = 0;
            foreach ($pops as $key => $value) {
                if ($value[0] > $id ) {
                    $id = $value[0]+1;
                }
            }
            $id +=1;
            $p = new Pops($id);
            $species = array();
            foreach ($pops as $key => $value) {
                if ($value[2] == $this->id) {
                    array_push($species,$value[1]);
                }
            }
            $key = array_rand($species,1);
            $p->newPop($species[$key],$this->id);
            array_push($this->pops,$id);
            $this->popGrowthProcess = $this->popGrowthProcess-100;
        }
        $this->updatePlanet();
    }
    //贸易中心搜索//
    function searchTradeHub() {
        $stars = Star::get()->toArray();
        foreach ($stars as $key => $value) {
            $stars[$key]['hyperlane'] = json_decode($value['hyperlane'],true);
        }
        $getFlag = False;
        $searchArray = array();
        $searchArray[] = $this->position;
        $nextArray = array();
        foreach ($stars[$this->position]['hyperlane'] as $key => $value) {
            $nextArray[] = $value['to'];
        }
        while ($getFlag != true) {
            foreach ($nextArray as $key => $value) {

            }
        }

    }


}
