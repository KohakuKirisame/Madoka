<?php

namespace App\Http\Controllers;
use App\Models\Army;
use App\Models\Country;
use App\Models\Definition;
use App\Models\Planet;
use App\Models\PlanetType;
use App\Models\Population;
use App\Models\District;
use App\Models\Job;
use App\Models\Species;
use App\Models\Star;
use App\Models\Station;
use App\Models\User;
use App\Models\Good;
use Illuminate\Http\Request;

class PlanetController extends Controller {

    public int $id,$position,$size,$nearTradeHub,$tradeHubDistance;
    public string $name,$type,$owner,$controller,$leaderParty,$popGrowthProcess;
    public array $pops;
    public array $districts;
    public array $product;

    function __construct($id=-1) {
        if($id!=-1){
            $p =Planet::where(["id"=>$id])->first();
            $this->id = $id;
            $this->name = $p ->name;
            $this->position = $p->position;
            $this->owner = $p->owner;
            $this->controller = $p->controller;
        }

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
        foreach ($this->product['market'] as $key => $value) {
            $this->product['market'][$key] = 0;
            $this->product['country'][$key] = 0;
        }
        foreach ($this->districts as $key => $value) {
            if ($value['ownership'] != 3) {
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
        $this->updatePlanet();
    }
    //主权变更//
    public function colonize(Request $request) {
        $uid = $request->session()->get('uid');
        $user= UserController::GetInfo($uid);
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        $id = $request->input('id');
        $planet = Planet::where(["id"=>$id])->first();
        $star = Star::where(["id"=>$planet->position])->first();
        $rights = json_decode(Country::where(["tag"=>$id])->first()->species,true);
        foreach ($rights as $specie) {
            if ($specie['right'] == 1) {
                $species = $specie['name'];
                break;
            }
        }
        if ($star->owner == $country) {
            $planet->owner = $country;
            $planet->controller = $country;
            $pop1 = new Population();
            $pop1->species = $species;
            $pop1->position = $id;
            $pop1->job = '无';
            $pop1->class = 'low';
            $pop1->workat = '无';
            $pop1->ethic = '';
            $pop1->ig = '';
            $pop1->party = '';
            $pop1->cash = 20;
            $pop1->struggle = 0;
            $pop1->save();
            $pop2 = new Population();
            $pop2->species = $species;
            $pop2->position = $id;
            $pop2->job = '无';
            $pop2->class = 'low';
            $pop2->workat = '无';
            $pop2->ethic = '';
            $pop2->ig = '';
            $pop2->party = '';
            $pop2->cash = 20;
            $pop2->struggle = 0;
            $pop2->save();
            $pops = [$pop1->id,$pop2->id,];
            $planet->pops = json_encode($pops,JSON_UNESCAPED_UNICODE);
            $planet->save();
            $country = Country::where(["tag"=>$country])->first();
            $planets = json_decode($country->planets,true);
            $planets[] = $planet->id;
            $country->planets = json_encode($planets,JSON_UNESCAPED_UNICODE);
            $country->save();
            $market = new MarketController($country->tag);
            $market->planets[] = $planet->$id;
            $market->UpdateMarket();
        }
    }
    function beOccupied($controller) {
        Planet::where(["id"=>$this->id])->update(["controller"=>$controller]);
        echo "殖民地信息已更新\n";
    }
    //区划计算//
    function districtCount() {
        $market = new MarketController($this->controller);
        $country = Country::where(["tag" => $this->owner])->first();
        $country->species = json_decode($country->species,true);
        $dTax = $country->districtTax;
        $upTax = $country->upTax;
        $midTax = $country->midTax;
        $lowTax = $country->lowTax;
        $jobData = Job::get()->toArray();
        foreach ($this->districts as $key => $value) {
            $disData = District::where(["name" => $value['name']])->first();
            if ($value['name'] == '行政区划') {
                if ($value['size'] != floor(count($this->pops) / 10)) {
                    $this->districts[$key]['size'] = floor(count($this->pops) / 10);
                }
                $this->districts[$key]['profit'] = 10000;
                if ($country->economyType == 1) {
                    continue;
                }
                foreach ($value['jobs'] as $key2 => $value2) {
                    if ($key2 == 'upJob') {
                        $this->product['country']['energy'] -= 2 * 2.5*$market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['upJob']);
                        foreach ($value2 as $key3 => $value3) {
                            $pop = Population::where(["id" => $value3])->first();
                            $cash = $pop->cash;
                            $speciesType = Species::where(["name"=>$pop->species])->first()->type;
                            if ($speciesType == 'robot') {
                                $this->product['country']['energy'] -= 1;
                                continue;
                            }
                            $cash += 2 * 2.5*2.5*$market->goods['consume_goods']['price'];
                            $cash -= $upTax * 2 * 2.5*$market->goods['consume_goods']['price'];
                            Population::where(["id" => $value3])->update(["cash"=>$cash]);
                        }
                    } elseif ($key2 == 'midJob') {
                        $this->product['country']['energy'] -= 1.5 * count($this->districts[$key]['jobs']['midJob']);
                        foreach ($value2 as $key3 => $value3) {
                            $pop = Population::where(["id" => $value3])->first();
                            $cash = $pop->cash;
                            $speciesType = Species::where(["name"=>$pop->species])->first()->type;
                            if ($speciesType == 'robot') {
                                $this->product['country']['energy'] -= 1;
                                continue;
                            }
                            $cash += 1.5 * 2.5*$market->goods['consume_goods']['price'];
                            $cash -= $midTax * 1.5 * 2.5*$market->goods['consume_goods']['price'];
                            Population::where(["id" => $value3])->update(["cash"=>$cash]);
                        }
                    } else {
                        $this->product['country']['energy'] -= 1 * count($this->districts[$key]['jobs']['upJob']);
                        foreach ($value2 as $key3 => $value3) {
                            $pop = Population::where(["id" => $value3])->first();
                            $cash = $pop->cash;
                            $speciesType = Species::where(["name" => $pop->species])->first()->type;
                            if ($speciesType == 'robot') {
                                $this->product['country']['energy'] -= 1;
                                continue;
                            }
                            $cash += 1 * 2.5 * $market->goods['consume_goods']['price'];
                            $cash -= $lowTax * 1 * 2.5 * $market->goods['consume_goods']['price'];
                            Population::where(["id" => $value3])->update(["cash" => $cash]);
                        }
                    }
                }
                continue;
            }
            foreach ($this->districts[$key]['product'] as $key2 =>$value2) {
                $this->districts[$key]['product'][$key2] = 0;
            }
            foreach ($value['jobs'] as $key2 => $value2) {
                foreach ($value2 as $key3 => $value3) {
                    echo($value3);
                    $pop = Population::where(["id" => $value3])->first();
                    foreach ($jobData as $data) {
                        if ($data['name'] == $pop->job) {
                            $demand = json_decode($data['demand'], true);
                            $supply = json_decode($data['supply'], true);
                            foreach ($demand as $goods => $value4) {
                                $modifierName = Definition::where(["area" => "economy", "economyKey" => "consume", "modifierKey" => $goods])->first()->modifierName;
                                $modifier = 1 + Country::where(["tag" => $this->owner])->first()->$modifierName;
                                $this->districts[$key]['product'][$goods] -= $value4 * $modifier;
                            }
                            foreach ($supply as $goods => $value4) {
                                $modifierName = Definition::where(["area" => "economy", "economyKey" => "produce", "modifierKey" => $goods])->first()->modifierName;
                                $modifier = 1 + Country::where(["tag" => $this->owner])->first()->$modifierName;
                                $this->districts[$key]['product'][$goods] += $value4 * $modifier;
                            }
                        }
                    }
                    foreach ($country->species as $specie) {
                        if ($specie['name'] == $pop->species && $specie['right'] == 0) {
                            $needs = json_decode(Species::where(["name"=>$pop->species])->first()->needs,true);
                            foreach ($needs['low'] as $goods => $num) {
                                $this->districts[$key]['product'][$goods] -= $value;
                            }
                        }
                    }
                }
            }
            $cash0 = $this->districts[$key]['cash'];
            if ($this->districts[$key]['cash'] <= -100) {
                foreach($this->districts[$key]['jobs']['lowJob'] as $p) {
                    $pop = Population::where(["id"=>$p])->first();
                    foreach ($country->species as $specie) {
                        if ($specie['name'] == $pop->species && $specie['right'] == 0) {
                            $p->update(["job" => "无","class"=>"low","workat" => "无"]);
                            $this->districts[$key]['cash'] += 500;
                            break;
                        }
                    }
                }
            }
            if ($this->districts[$key]['cash'] <= -500) {
                if ($this->districts[$key]['size'] < 2) {
                    $this->districts[$key]['size'] = 0;
                    foreach ($this->districts[$key]['jobs'] as $key2 => $value2) {
                        foreach ($value2 as $key3 => $value3) {
                            Population::where(["id" => $value3])->update(["job" => "无", "class" => "low", "workat" => "无"]);
                        }
                    }
                    array_splice($this->districts,$key,1);
                }
                else {
                    $this->districts[$key]['size'] -= 1;
                    $this->districts[$key]['cash'] += $disData->buildCost;
                }
            }
            else {
                if ($country->economyType == 1) {
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
                            $this->product['country']['energy'] -= 2 * 2.5*$market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['upJob']);
                            foreach ($value2 as $key3 => $value3) {
                                $pop = Population::where(["id" => $value3])->first();
                                $cash = $pop->cash;
                                $speciesType = Species::where(["name"=>$pop->species])->first()->type;
                                if ($speciesType == 'robot') {
                                    $this->product['country']['energy'] -= 1;
                                    continue;
                                }
                                $salary = 2 * 2.5*$market->goods['consume_goods']['price'];
                                $cash += (1 - $upTax) * $salary;
                                $this->product['country']['energy'] += $upTax * $salary;
                                Population::where(["id" => $value3])->update(["cash" => $cash]);
                            }
                        } elseif ($key2 == 'midJob') {
                            $this->product['country']['energy'] -= 1.5 * count($this->districts[$key]['jobs']['midJob']);
                            foreach ($value2 as $key3 => $value3) {
                                $pop = Population::where(["id" => $value3])->first();
                            $cash = $pop->cash;
                            $speciesType = Species::where(["name"=>$pop->species])->first()->type;
                            if ($speciesType == 'robot') {
                                $this->product['country']['energy'] -= 1;
                                continue;
                            }
                                $salary = 1.5 * 2.5*$market->goods['consume_goods']['price'];
                                $cash += (1 - $midTax) * $salary;
                                $this->product['country']['energy'] += $upTax * $salary;
                                Population::where(["id" => $value3])->update(["cash" => $cash]);
                            }
                        } else {
                            foreach ($country->species as $specie) {
                                if ($specie['name'] == $pop->species && $specie['right'] == 0) {
                                    continue 2;
                                }
                            }
                            $this->product['country']['energy'] -= 1 * count($this->districts[$key]['jobs']['upJob']);
                            foreach ($value2 as $key3 => $value3) {
                                $pop = Population::where(["id" => $value3])->first();
                            $cash = $pop->cash;
                            $speciesType = Species::where(["name"=>$pop->species])->first()->type;
                            if ($speciesType == 'robot') {
                                $this->product['country']['energy'] -= 1;
                                continue;
                            }
                                $salary = 1 * 2.5*$market->goods['consume_goods']['price'];
                                $cash += (1 - $lowTax) * $salary;
                                $this->product['country']['energy'] += $upTax * $salary;
                                Population::where(["id" => $value3])->update(["cash" => $cash]);
                            }
                        }
                    }
                    else {
                        if ($key2 == 'upJob') {
                            $this->districts[$key]['cash'] -= 2 * 2.5*$market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['upJob']);
                            foreach ($value2 as $key3 => $value3) {
                                $pop = Population::where(["id" => $value3])->first();
                                $cash = $pop->cash;
                                $speciesType = Species::where(["name"=>$pop->species])->first()->type;
                                if ($speciesType == 'robot') {
                                    $this->product['country']['energy'] -= 1;
                                    continue;
                                }
                                    $salary = 2 * 2.5*$market->goods['consume_goods']['price'];
                                    $cash += (1 - $upTax) * $salary;
                                    $this->product['country']['energy'] += $upTax * $salary;
                                    Population::where(["id" => $value3])->update(["cash" => $cash]);
                            }
                        } elseif ($key2 == 'midJob') {
                            if ($this->districts[$key]['cash'] - $cash0 < 0) {
                                $this->districts[$key]['cash'] -= 1 * 2.5*$market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['upJob']);
                                foreach ($value2 as $key3 => $value3) {
                                    $pop = Population::where(["id" => $value3])->first();
                                    $cash = $pop->cash;
                                    $speciesType = Species::where(["name"=>$pop->species])->first()->type;
                                    if ($speciesType == 'robot') {
                                        $this->product['country']['energy'] -= 1;
                                        continue;
                                    }
                                    $salary = 1 * 2.5*$market->goods['consume_goods']['price'];
                                    $cash += (1 - $midTax) * $salary;
                                    $this->product['country']['energy'] += $upTax * $salary;
                                    Population::where(["id" => $value3])->update(["cash" => $cash]);
                                }
                            } else {
                                $this->districts[$key]['cash'] -= 1.5 * count($this->districts[$key]['jobs']['midJob']);
                                foreach ($value2 as $key3 => $value3) {
                                    $pop = Population::where(["id" => $value3])->first();
                                    $cash = $pop->cash;
                                    $speciesType = Species::where(["name"=>$pop->species])->first()->type;
                                    if ($speciesType == 'robot') {
                                        $this->product['country']['energy'] -= 1;
                                        continue;
                                    }
                                    $salary = 1.5 * 2.5*$market->goods['consume_goods']['price'];
                                    $cash += (1 - $midTax) * $salary;
                                    $this->product['country']['energy'] += $upTax * $salary;
                                    Population::where(["id" => $value3])->update(["cash" => $cash]);
                                }
                            }
                        } else {
                            if ($this->districts[$key]['cash'] - $cash0 < 0) {
                                $this->districts[$key]['cash'] -= 0.4 * 2.5*$market->goods['consume_goods']['price'] * count($this->districts[$key]['jobs']['lowJob']);
                                foreach ($value2 as $key3 => $value3) {
                                    $pop = Population::where(["id" => $value3])->first();
                                    $cash = $pop->cash;
                                    $speciesType = Species::where(["name"=>$pop->species])->first()->type;
                                    foreach ($country->species as $specie) {
                                        if ($specie['name'] == $pop->species && $specie['right'] == 0) {
                                            continue 3;
                                        }
                                    }
                                    if ($speciesType == 'robot') {
                                        $this->product['country']['energy'] -= 1;
                                        continue;
                                    }
                                    $salary = 0.4 * 2.5*$market->goods['consume_goods']['price'];
                                    $cash += (1 - $lowTax) * $salary;
                                    $this->product['country']['energy'] += $upTax * $salary;
                                    Population::where(["id" => $value3])->update(["cash" => $cash]);
                                }
                            } else {
                                $this->districts[$key]['cash'] -= 1 * count($this->districts[$key]['jobs']['upJob']);
                                foreach ($value2 as $key3 => $value3) {
                                    $pop = Population::where(["id" => $value3])->first();
                                    $cash = $pop->cash;
                                    $speciesType = Species::where(["name"=>$pop->species])->first()->type;
                                    foreach ($country->species as $specie) {
                                        if ($specie['name'] == $pop->species && $specie['right'] == 0) {
                                            continue 3;
                                        }
                                    }
                                    if ($speciesType == 'robot') {
                                        $this->product['country']['energy'] -= 1;
                                        continue;
                                    }
                                    $salary = 1 * 2.5*$market->goods['consume_goods']['price'];
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
                if ((($sizeAll += 1) < $this->size) && ($this->districts[$key]['cash'] > 2 * $disData->buildCost)) {
                    echo "!!!";
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
            if ($value['name'] == '行政区划') {
                continue;
            }
            $profitArray = array_merge($profitArray,[$value['name'] => $value['profit']]);
        }
        arsort($profitArray);
        $country = Country::where(["tag" => $this->owner])->first();
        foreach ($profitArray as $key => $value) {
            $disData = District::where(["name" => $key])->first();
            if ($disData->buildCost <= $country->cashPool && ($sizeAll+1)<=$this->size) {
                foreach ($this->districts as $key2=>$value2) {
                    if ($value2['name'] == $key) {
                        $this->districts[$key2]['size'] += 1;
                        $country->cashPool -= $disData->buildCost;
                        break 2;
                    }
                }
            }
        }
        $this->updatePlanet();
        Country::where(["tag" => $this->owner])->update(["cashPool" => $country->cashPool]);
    }
    //区划建造//
    public function buildDistrict(Request $request) {
        $uid = $request->session()->get('uid');
        $User =User::where('uid',$uid)->first();
        $privilege = $User->privilege;
        $id = $request->input('id');
        $district = $request->input('district');
        $cost = District::where(["name" => $district])->first()->baseCost;
        $planet = Planet::where(["id" => $id])->first();
        $owner = $planet->owner;
        $energy = Country::where(["tag" => $owner])->first()->energy;
        if ($energy < $cost+200 && $privilege == 2) {
            return 0;
        }
        else {
            if ($privilege == 2) {
                $energy -= $cost + 200;
            }
            $districts = json_decode($planet->districts, true);
            $isExisted = false;
            foreach ($districts as $key => $value) {
                if ($district == $value['name'] && $value['ownership'] == 2) {
                    $districts[$key]['size'] += 1;
                    $districts[$key]['cash'] += 200;
                    $isExisted = true;
                    break;
                }
            }
            if (!$isExisted) {
                $districts[] = ["name" => $district, "size" => 1, "cash" => 200, "ownership" => 2, "profit" => 0,
                    "jobs" => ["upJob" => [], "midJob" => [], "lowJob" => []],"product"=>["zro"=>0,"gases"=>0, "grain"=>0, "motes"=>0, "satra"=>0, "alloys"=>0, "crystals"=>0, "minerals"=>0, "consume_goods"=>0]];
            }
        }
        $planet->districts = json_encode($districts,JSON_UNESCAPED_UNICODE);
        $planet->save();
//        Planet::where(["id" => $id])->update(["districts" => $planet->districts]);
    }
    public function buildMarketDistrict(Request $request) {
        $uid = $request->session()->get('uid');
        $User =User::where('uid',$uid)->first();
        $privilege = $User->privilege;
        $id = $request->input('id');
        $district = $request->input('district');
        $planet = Planet::where(["id" => $id])->first();
        $owner = $planet->owner;
        if ($privilege <= 1) {
            $districts = json_decode($planet->districts, true);
            $isExisted = false;
            foreach ($districts as $key => $value) {
                if ($district == $value['name'] && $value['ownership'] == 0) {
                    $districts[$key]['size'] += 1;
                    $isExisted = true;
                    break;
                }
            }
            if (!$isExisted) {
                $districts[] = ["name" => $district, "size" => 1, "cash" => 200, "ownership" => 0, "profit" => 0,
                    "jobs" => ["upJob" => [], "midJob" => [], "lowJob" => []],"product"=>["zro"=>0,"gases"=>0, "grain"=>0, "motes"=>0, "satra"=>0, "alloys"=>0, "crystals"=>0, "minerals"=>0, "consume_goods"=>0]];
            }
        }
        $planet->districts = json_encode($districts,JSON_UNESCAPED_UNICODE);
        $planet->save();
//        Planet::where(["id" => $id])->update(["districts" => $planet->districts]);
    }
    //人口增长//
    function popGrowth() {
        $typeD = PlanetType::where(["name"=>$this->type])->first();
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
            $species = [];
            foreach ($this->pops as $pop) {
                $species[] = Population::where(["id"=>$pop])->first()->species;
            }
            if (count($species) == 0) {
                return;
            }
            $key = array_rand($species,1);
            $pop = new Population();
            $pop->species = $species[$key];
            $pop->position = $this->id;
            $pop->job = '无';
            $pop->class = 'low';
            $pop->workat = '无';
            $pop->ethic = '';
            $pop->ig = '';
            $pop->party = '';
            $pop->cash = 20;
            $pop->struggle = 0;
            $pop->save();
            $this->pops[] = $pop->id;
            $this->popGrowthProcess = $this->popGrowthProcess-100;
        }
        $this->updatePlanet();
    }
    //贸易中心搜索//
    function searchNearestHub($hubArray) {
        $hypers = Star::get()->toArray();
        $hyperLanes=$hypers[$this->position]['hyperlane'];
        $hyperLanes=json_decode($hyperLanes,true);
        $queue = [];
        if(Star::where("id",$this->position)->first()->isTradeHub==1){
            return([$this->position,0]);
        }
        $depth=1;
        $isReached = false;
        $i = 0;
        while($i<400){
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
                $hyperLanes=$hypers[$start[0]]['hyperlane'];
                $hyperLanes=json_decode($hyperLanes,true);
                $depth=$start[1]+1;
            }
            $i++;
        }
    }
    function searchTradeHub() {
        if ($this->owner == '' && $this->id == 1) {
            return 0;
        }
        $stars = Star::get()->toArray();
        $hubArray = [];
        foreach ($stars as $key => $value) {
            $stars[$key]['hyperlane'] = json_decode($value['hyperlane'],true);
            $isTradeHub = Star::where(["id" => $value['id']])->first()->isTradeHub;
            if ($stars[$key]['owner'] == $this->owner && $isTradeHub == 1 && $stars[$key]['owner'] != '') {
                $hubArray[] = $value['id'];
            }
        }
        if (count($hubArray) != 0) {
            $ans = $this->searchNearestHub($hubArray);
            if (!is_null($ans)) {
                $this->nearTradeHub = $ans[0];
                $this->tradeHubDistance = $ans[1];;
                $this->updatePlanet();
            }
        }else {
            return 0;
        }
    }
    //////////
    public function planetPage(Request $request){
        $uid = $request->session()->get('uid');
        $User =User::where('uid',$uid)->first();
        $privilege = $User->privilege;
        $country = $User->country;
        $user=UserController::GetInfo($uid);
        if (key_exists("Err",$user)){
            return redirect("/Action/Logout");
        }
        if($country!="" && $privilege == 2){
            $planets = Planet::where("owner",$country)->paginate(10);
        }else{
            $planets = Planet::paginate(16);
        }
        foreach ($planets as $planet) {
//            if (is_null($planet['energy'])) {
//                $p = Planet::where("id",$planet['id'])->first();
//                $resource = json_decode(Country::where("tag",$planet['owner'])->first()->resource,true);
//                foreach ($resource as $key => $resource) {
//                    Planet::where("id",$planet['id'])->update([$key => $resource*$p->weight]);
//                }
//            }
            $planet['position'] = Star::where(["id"=>$planet['position']])->first()->name;
            $planet['resource'] = [];
            $planet['resource'] = array_merge($planet['resource'],["energy"=>$planet['energy']]);
            $planet['resource'] = array_merge($planet['resource'],["minerals"=>$planet['minerals']]);
            $planet['resource'] = array_merge($planet['resource'],["grain"=>$planet['grain']]);
            $planet['resource'] = array_merge($planet['resource'],["consume_goods"=>$planet['consume_goods']]);
            $planet['resource'] = array_merge($planet['resource'],["alloys"=>$planet['alloys']]);
            $planet['resource'] = array_merge($planet['resource'],["gases"=>$planet['gases']]);
            $planet['resource'] = array_merge($planet['resource'],["motes"=>$planet['motes']]);
            $planet['resource'] = array_merge($planet['resource'],["crystals"=>$planet['crystals']]);
            $planet['resource'] = array_merge($planet['resource'],["zro"=>$planet['zro']]);
            $planet['resource'] = array_merge($planet['resource'],["satra"=>$planet['satra']]);

        }
        return view('planet',["user"=>$user,"privilege"=>$privilege,
                                "planets"=>$planets]);
    }
    public function adminNewPop(Request $request) {
        $uid = $request->session()->get('uid');
        $privilege=User::where('uid',$uid)->first()->privilege;
        $id = $request->input('id');
        $species = $request->input("species");
        if ($privilege <=1) {
            $pop = new Population();
            $pop->species = $species;
            $pop->position = $id;
            $pop->job = '无';
            $pop->class = 'low';
            $pop->workat = '无';
            $pop->ethic = '';
            $pop->ig = '';
            $pop->party = '';
            $pop->cash = 20;
            $pop->struggle = 0;
            $pop->save();
            $pops = json_decode(Planet::where('id',$id)->first()->pops,true);
            $pops[] = $pop->id;
            Planet::where('id',$id)->update(["pops"=>json_encode($pops,JSON_UNESCAPED_UNICODE)]);
        }
    }
    public function readPlanet(Request $request) {
        $id = $request->input('id');
        $planet = Planet::where('id',$id)->first()->toArray();
        $planet['districts'] = json_decode($planet['districts'],true);
        $planet['product'] = json_decode($planet['product'],true);
        $planet['pops'] = json_decode($planet['pops'],true);
        $pops = [];
        foreach ($planet['pops'] as $pop) {
            $pop = Population::where('id',$pop)->first();
            $pops[] = [$pop->id,$pop->species,$pop->job,];
        }
        $output=["name"=>$planet["name"],"districts"=>$planet['districts'],"pops"=>$pops,"product"=>$planet['product']];
        $output = json_encode($output,JSON_UNESCAPED_UNICODE);
        return $output;
        //Here's to the crazy ones.
    }
    public function changeSize(Request $request) {
        $uid = $request->session()->get('uid');
        $privilege=User::where('uid',$uid)->first()->privilege;
        $id = $request->input('id');
        $size = $request->input("size");
        if ($privilege == 0 || $privilege == 1) {
            Planet::where('id',$id)->update(["size" => $size]);
        }
    }
    public function changePlanetName(Request $request) {
        $id = $request->input('id');
        $name = $request->input("name");
        Planet::where('id', $id)->update(["name"=>$name]);
    }
    public function buildArmy(Request $request) {
        $uid = $request->session()->get('uid');
        $user= UserController::GetInfo($uid);
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $id = $request->input('id');
        $planet = Planet::where(["id"=>$id])->first();
        $army = Army::where('position',$planet->position)->first();
        if ($privilege <= 1) {
            $Country = Country::where('tag', $planet->owner)->first();
            if (is_null($army)) {
                $armyNew = new Army();
                $armyNew->position = $planet->position;
                $armyNew->name = $planet->name.'特遣队';
                $armyNew->owner = $planet->owner;
                $armyNew->quantity = 1;
                $armyNew->HP = 100*(1+$Country->armyHPModifier);
                $armyNew->damage = 10*(1+$Country->armyDamageModifier);
                $armyNew->moving ='[]';
                $armyNew->save();
            } else {
                $army->quantity += 1;
                $army->HP *= 2;
                $army->damage *= 2;
                $army->save();
            }
        } elseif ($privilege ==2) {
            $country = $MadokaUser->country;
            $Country = Country::where('tag', $country)->first();
            if ($country= $planet->owner) {
                $Country->energy -= 100;
                if (is_null($army)) {
                    $armyNew = new Army();
                    $armyNew->position = $planet->position;
                    $armyNew->name = $planet->name.'特遣队';
                    $armyNew->owner = $planet->owner;
                    $armyNew->quantity = 1;
                    $armyNew->HP = 100*(1+$Country->armyHPModifier);
                    $armyNew->damage = 10*(1+$Country->armyDamageModifier);
                    $armyNew->moving ='[]';
                    $armyNew->save();
                } else {
                    $army->quantity += 1;
                    $army->HP *= 2;
                    $army->damage *= 2;
                    $army->save();
                }
            }
        }
    }
    public function adminDeletePop(Request $request) {
        $uid = $request->session()->get('uid');
        $user= UserController::GetInfo($uid);
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        $popid = $request->input('id');
        $planet = $request->input('planet');
        if ($privilege <= 1) {
            $pop = Population::where(["id"=>$popid])->first();
            $pop->delete();
            $planet = Planet::where(["id"=>$planet])->first();
            $pops = json_decode($planet->pops,true);
            foreach ($pops as $key=>$value) {
                if ($value == $popid) {
                    array_splice($pops,$key,1);
                    $planet->pops = json_encode($pops,JSON_UNESCAPED_UNICODE);
                    $planet->save();
                    break;
                }
            }
        }
    }
    public function planetCount(Request $request) {
        $uid = $request->session()->get('uid');
        $user= UserController::GetInfo($uid);
        if(key_exists('Err',$user)){
            return redirect('/Action/Logout');
        }
        $MadokaUser = User::where(["uid"=>$uid])->first();
        $privilege = $MadokaUser->privilege;
        if($privilege <= 1) {
            $id = $request->input('id');
            $planet = new PlanetController($id);
            $planet->searchTradeHub();
            $planet->districtCount();
            $planet->investDistrict();
            $planet->countRes();
            $planet->updatePlanet();
            foreach($planet->pops as $pop) {
                $pop = new PopController($pop);
                $pop->findJob();
            }
            $m = new MarketController($planet->owner);
            $m->priceCount();
            $m->updateMarket();
        }
    }
}
