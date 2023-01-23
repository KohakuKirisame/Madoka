<?php

namespace app\Http\Controllers;
use App\Models\Country;
use App\Models\Definition;
use app\Models\Planet;
use app\Models\PlanetType;
use app\Models\Population;
use app\models\District;
use app\models\Job;
use app\Models\Star;
use App\Models\Station;

class PlanetController extends Controller {

    public int $id,$position,$size,$nearTradeHub,$tradeHubDistance;
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
        $this->nearTradeHub = $p->nearTradeHub;
        $this->tradeHubDistance = $p->tradeHubDistance;
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
            $this->product['market'][$key] = 0;
            $this->product['country'][$key] = 0;
        }
        foreach ($this->districts as $key => $value) {
            if ($value['ownerShip'] != 3) {
                foreach ($value['product'] as $key2 => $value2) {
                    $this->product['market'][$key2] += $value2;
                }
            }
            else {
                foreach ($value['product'] as $key2 => $value2) {
                    $this->product['country'][$key2] += $value2;
                }
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
        $result = Country::where(["tag" => $this->owner]) - first();
        $dTax = $result->districtTax;
        $upTax = $result->upTax;
        $midTax = $result->midTax;
        $lowTax = $result->lowTax;
        $jobData = Job::get()->toArray();
        foreach ($this->districts as $key => $value) {
            $disData = District::where(["name" => $key])->first;
            if ($key == '行政区划') {
                if ($value['size'] != floor(count($this->pops) / 10)) {
                    $value['size'] = floor(count($this->pops) / 10);
                }
                $value['profit'] = 10000;
                if ($result->economyType == 1) {
                    continue;
                }
                foreach ($value['jobs'] as $key2 => $value2) {
                    if ($key2 == 'upJob') {
                        $this->product['country']['energy'] -= 2 * $market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['upJob']);
                        foreach ($value2 as $key3 => $value3) {
                            $cash = Population::where(["id" => $value3])->first()->cash;
                            $cash += 2 * $market->goods['consume_goods']['price'];
                            $cash -= $upTax * 2 * $market->goods['consume_goods']['price'];
                            Population::where(["id" => $value3])->update("cash", $cash);
                        }
                    } elseif ($key2 == 'midJob') {
                        $this->product['country']['energy'] -= 1.5 * count($this->districts[$key]['jobs']['midJob']);
                        foreach ($value2 as $key3 => $value3) {
                            $cash = Population::where(["id" => $value3])->first()->cash;
                            $cash += 1.5 * $market->goods['consume_goods']['price'];
                            $cash -= $midTax * 1.5 * $market->goods['consume_goods']['price'];
                            Population::where(["id" => $value3])->update("cash", $cash);
                        }
                    } else {
                        $this->product['country']['energy'] -= 1 * count($this->districts[$key]['jobs']['upJob']);
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
            foreach ($value['jobs'] as $key2 => $value2) {
                foreach ($value2 as $key3 => $value3) {
                    $popJob = Population::where(["id" => $value3])->first()->job;
                    foreach ($jobData as $data) {
                        if ($data['name'] == $popJob) {
                            $demand = json_decode($data['demand'], true);
                            $supply = json_decode($data['supply'], true);
                            foreach ($demand as $goods => $value4) {
                                $modifierName = Definition::where(["area" => "economy", "economyKey" => "consume", "modifierKey" => $key2])->first()->modifierName;
                                $modifier = 1 + Country::where(["tag" => $this->owner])->first()->$modifierName;
                                $this->districts[$key]['product'][$goods] -= $value4 * $modifier;
                            }
                            foreach ($supply as $goods => $value4) {
                                $modifierName = Definition::where(["area" => "economy", "economyKey" => "produce", "modifierKey" => $key2])->first()->modifierName;
                                $modifier = 1 + Country::where(["tag" => $this->owner])->first()->$modifierName;
                                $this->districts[$key]['product'][$goods] += $value4 * $modifier;
                            }
                        }
                    }
                }
            }
            $cash0 = $this->districts[$key]['cash'];
            if ($this->districts[$key]['cash'] <= -500) {
                if ($this->districts[$key]['size'] < 2) {
                    $this->districts[$key]['size'] = 0;
                    foreach ($this->districts[$key]['jobs'] as $key2 => $value2) {
                        foreach ($value2 as $key3 => $value3) {
                            Population::where(["id" => $value])->update(["job" => "无","class"=>"low","workat" => "无"]);
                        }
                    }
                }
                else {
                    $this->districts[$key]['size'] -= 1;
                }
            }
            else {
                if ($result->economyType == 1) {
                    continue;
                }
                foreach ($this->districts[$key]['product'] as $key2 => $value2) {
                    if ($value2 > 0) {
                        $this->districts[$key]['cash'] += $value2 * $market->goods[$key2]['price']*(1-($this->tradeHubDistance*0.05));
                    } else {
                        $this->districts[$key]['cash'] -= $value2 * $market->goods[$key2]['price']*(1+($this->tradeHubDistance*0.05));
                    }
                }
                foreach ($this->districts[$key]['jobs'] as $key2 => $value2) {
                    if ($value['ownership'] == 3) {
                        if ($key2 == 'upJob') {
                            $this->product['country']['energy'] -= 2 * $market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['upJob']);
                            foreach ($value2 as $key3 => $value3) {
                                $cash = Population::where(["id" => $value3])->first()->cash;
                                $salary = 2 * $market->goods['consume_goods']['price'];
                                $cash += (1 - $upTax) * $salary;
                                $this->product['country']['energy'] += $upTax * $salary;
                                Population::where(["id" => $value3])->update(["cash" => $cash]);
                            }
                        } elseif ($key2 == 'midJob') {
                            $this->product['country']['energy'] -= 1.5 * count($this->districts[$key]['jobs']['midJob']);
                            foreach ($value2 as $key3 => $value3) {
                                $cash = Population::where(["id" => $value3])->first()->cash;
                                $salary = 1.5 * $market->goods['consume_goods']['price'];
                                $cash += (1 - $midTax) * $salary;
                                $this->product['country']['energy'] += $upTax * $salary;
                                Population::where(["id" => $value3])->update(["cash" => $cash]);
                            }
                        } else {
                            $this->product['country']['energy'] -= 1 * count($this->districts[$key]['jobs']['upJob']);
                            foreach ($value2 as $key3 => $value3) {
                                $cash = Population::where(["id" => $value3])->first()->cash;
                                $salary = 1 * $market->goods['consume_goods']['price'];
                                $cash += (1 - $lowTax) * $salary;
                                $this->product['country']['energy'] += $upTax * $salary;
                                Population::where(["id" => $value3])->update(["cash" => $cash]);
                            }
                        }
                    }
                    else {
                        if ($key2 == 'upJob') {
                            $this->districts[$key]['cash'] -= 2 * $market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['upJob']);
                            foreach ($value2 as $key3 => $value3) {
                                $cash = Population::where(["id" => $value3])->first()->cash;
                                $salary = 2 * $market->goods['consume_goods']['price'];
                                $cash += (1 - $upTax) * $salary;
                                $this->product['country']['energy'] += $upTax * $salary;
                                Population::where(["id" => $value3])->update(["cash" => $cash]);
                            }
                        } elseif ($key2 == 'midJob') {
                            if ($this->districts[$key]['cash'] - $cash0 < 0) {
                                $this->districts[$key]['cash'] -= 1 * $market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['upJob']);
                                foreach ($value2 as $key3 => $value3) {
                                    $cash = Population::where(["id" => $value3])->first()->cash;
                                    $salary = 1 * $market->goods['consume_goods']['price'];
                                    $cash += (1 - $midTax) * $salary;
                                    $this->product['country']['energy'] += $upTax * $salary;
                                    Population::where(["id" => $value3])->update(["cash" => $cash]);
                                }
                            } else {
                                $this->districts[$key]['cash'] -= 1.5 * count($this->districts[$key]['jobs']['midJob']);
                                foreach ($value2 as $key3 => $value3) {
                                    $cash = Population::where(["id" => $value3])->first()->cash;
                                    $salary = 1.5 * $market->goods['consume_goods']['price'];
                                    $cash += (1 - $midTax) * $salary;
                                    $this->product['country']['energy'] += $upTax * $salary;
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
                                    $this->product['country']['energy'] += $upTax * $salary;
                                    Population::where(["id" => $value3])->update(["cash" => $cash]);
                                }
                            } else {
                                $this->districts[$key]['cash'] -= 1 * count($this->districts[$key]['jobs']['upJob']);
                                foreach ($value2 as $key3 => $value3) {
                                    $cash = Population::where(["id" => $value3])->first()->cash;
                                    $salary = 1 * $market->goods['consume_goods']['price'];
                                    $cash += (1 - $lowTax) * $salary;
                                    $this->product['country']['energy'] += $upTax * $salary;
                                    Population::where(["id" => $value3])->update(["cash" => $cash]);
                                }
                            }
                        }
                    }
                }
                if ($this->districts[$key]['ownership'] == 3) {
                    if ($this->districts[$key]['cash'] < 0) {
                        $energy = Country::where(["tag"=>$this->owner])->first()->energy;
                        Country::where(["tag"=>$this->owner])->update(["energy" => $energy+$this->districts[$key]['cash']]);
                        $this->districts[$key]['cash'] = 0;
                    }
                }
                if ($this->districts[$key]['cash'] - $cash0 < 0) {
                    $this->districts[$key]['profit'] = $this->districts[$key]['cash'] - $cash0;
                    continue;
                } else {
                    $this->districts[$key]['profit'] = $this->districts[$key]['cash'] - $cash0;
                    $tax = $dTax * ($this->districts[$key]['cash'] - $cash0);
                    $this->districts[$key]['cash'] -= $tax;
                    $this->product['country']['energy'] += $tax;
                }
                //////////
                $sizeAll = 0;
                foreach ($this->districts as $key2 => $value2) {
                    $sizeAll += $value2['size'];
                }
                if ($this->districts[$key]['ownership'] == 3) {
                    continue;
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
            if ($this->product['country']['energy'] < 0) {
                $energyProduce = -$this->product['country']['energy'];
                $modifier = Country::where(["tag" => $this->owner])->first()->energuProduceModifier;
                $this->product['country']['energy'] = $energyProduce * $modifier;
            }
            $this->countRes();
            $this->updatePlanet();
        }
    }
    //区划投资//
    function investDistrict() {
        $sizeAll = 0;
        foreach ($this->districts as $key2 => $value2) {
            $sizeAll += $value2['size'];
        }
        $profitArray = [];
        foreach ($this->districts as $key => $value) {
            $profitArray[] = [$key => $value['profit']];
        }
        $profitArray = arsort($profitArray);
        $country = Country::where(["tag" => $this->owner])->first;
        foreach ($profitArray as $key => $value) {
            $disData = District::where(["name" => $key])->first;
            if ($disData->buildCost <= $country->cashPool && ($sizeAll+1)<=$this->size) {
                $this->districts[$key]['size'] += 1;
                $country->cashPool -= $disData->buildCost;
                break;
            }
        }
        $this->updatePlanet();
        Country::where(["tag" => $this->owner])->update(["cashPool" => $country->cashPool]);
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
        if ($this->product['market']['consume_goods'] < 0) {
            $growth *= 1/sqrt(abs($this->product['market']['consume_goods']));
        }
        elseif ($this->product['market']['consume_goods'] > 0) {
            $growth *= sqrt($this->product['market']['consume_goods']);
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
    function searchNearestHub($hubArray) {
        $hyperLanes=Star::where("id",$this->position)->first()->hyperlane;
        $hyperLanes=json_decode($hyperLanes,true);
        $queue = [];
        if(Station::where("position",$this->position)->first()->isTradeHub==1){
            return([$this->position,0]);
        }
        $depth=1;
        while(true){
            foreach($hyperLanes as $hyperLane){
                $queue[] = [$hyperLane["to"],$depth];
                if (in_array($hyperLane["to"],$hubArray)){
                    $isReached=true;
                    $target=$hyperLane["to"];
                    break;
                }
            }
            if ($isReached){
                return([$target,$depth]);
            }else{
                $start=array_shift($queue);
                $hyperLanes=Star::where("id",$start[0])->first()->hyperlane;
                $hyperLanes=json_decode($hyperLanes,true);
                $depth=$start[1]+1;
            }
        }

    }
    function searchTradeHub() {
        $stars = Star::get()->toArray();
        $hubArray = [];
        foreach ($stars as $key => $value) {
            $stars[$key]['hyperlane'] = json_decode($value['hyperlane'],true);
            if ($stars[$key]['stationType'] != '' && $stars[$key]['stationType'] != 'outpost') {
                $isTradeHub = Station::where(["position" => $value['id']])->first()->isTradeHub;
                if ($stars[$key]['owner'] == $this->owner && $isTradeHub == 1) {
                    $hubArray[] = $value['id'];
                }
            }
        }
        $ans = $this->searchNearestHub($hubArray);
        $target = $ans[0];
        $length = $ans[1];
    }


}
