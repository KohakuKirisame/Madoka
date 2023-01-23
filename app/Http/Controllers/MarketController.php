<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Market;
use app\Models\Planet;
use app\Models\Star;
use App\Models\Station;

class MarketController extends Controller {
    public var $owner;
    public array $member,$planets,$trades,$goods;
    function __construct($country) {
        $m = Market::where(["owner"=>$country])->first();
        $this->owner = $country;
        if (is_null($m)) {
            $result = Market::get();
            foreach ($result as $item) {
                $members = json_decode($item->members, true);
                if (in_array($country,$members)) {
                    $m = Market::where(["owner"=>$item->owner]);
                    $this->owner = $item->owner;
                    break;
                }
            }
        }
        $this->member = json_decode($m->member,true);
        $this->planets = json_decode($m->planets,true);
        $this->trades = json_decode($m->trades,true);
        $this->goods = array('minerals' => json_decode($m->minerals,true));
        $this->goods += array('grain'=>json_decode($m->grain,true));
        $this->goods += array('consume_goods'=>json_decode($m->consume_goods,true));
        $this->goods += array('alloys'=>json_decode($m->alloys,true));
        $this->goods += array('gases'=>json_decode($m->gases,true));
        $this->goods += array('motes'=>json_decode($m->motes,true));
        $this->goods += array('crystals'=>json_decode($m->crystals,true));
    }
    function UpdateMarket(){
        $member = json_encode($this->member,JSON_UNESCAPED_UNICODE);
        $planets = json_encode($this->planets,JSON_UNESCAPED_UNICODE);
        $trades = json_encode($this->trades,JSON_UNESCAPED_UNICODE);
        Market::where(["owner"=>$this->owner])->update(["mamber"=>$member,"planets"=>$planets,"trades"=>$trades]);
        foreach ($this->goods as $key => $value) {
            $value = json_encode($value,JSON_UNESCAPED_UNICODE);
            Market::where(["owner"=>$this->owner])->update(["$key"=>$value]);
        }
    }
    //价格计算//
    function priceCount() {
        foreach ($this->goods as $key => $value) {
            $this->goods[$key]['supplyOrder'] = 0;
            $this->goods[$key]['demandOrder'] = 0;
        }
        foreach ($this->planets as $key => $value) {
            $product = json_decode(Planet::where(["id"=>$value])->first->product,true);
            foreach ($this->goods as $key2 => $value2) {
                if ($product[$key2] < 0) {
                    $this->goods[$key2]['demandOrder'] -= $product[$key2];
                }
                else {
                    $this->goods[$key2]['supplyOrder'] += $product[$key2];
                }
            }
        }
        $ecoType = Country::where(["tag"=>$this->owner])->first()->economyType;
        if ($ecoType == 0) {
            foreach ($this->trades as $key => $value) {
                if ($value['duration'] > 0) {
                    foreach ($value['content'] as $key2 => $value2) {
                        if ($value2 > 0 ) {
                            $this->goods[$key2]['supplyOrder'] += $value2;
                        }
                        else {
                            $this->goods[$key2]['demandOrder'] -= $value2;
                        }
                    }
                }
            }
        }
        foreach ($this->goods as $key => $value) {
            $basePrice = Good::where(["name"=>$key])->first()->basePrice;
            $DO = $this->goods[$key]['demandOrder'];
            $SO = $this->goods[$key]['supplyOrder'];
            if ($this->goods[$key]['storage'] > 0) {
                $SO += $this->goods[$key]['storage'];
            }
            else {
                $DO += $this->goods[$key]['storage'];
            }
            if ($DO == 0) {
                $this->goods[$key]['price'] = 0.1*$basePrice;
            }
            elseif ($SO == 0) {
                $this->goods[$key]['price'] = 5*$basePrice;
            }
            else {
                $this->goods[$key]['price'] = $basePrice*(1+0.75 * (($DO - $SO)/min($SO,$DO)));
                if ($this->goods[$key]['price'] > 5*$basePrice) {
                    $this->goods[$key]['price'] = 5*$basePrice;
                }
                elseif ($this->goods[$key]['price'] < 0.1*$basePrice) {
                    $this->goods[$key]['price'] = 0.1*$basePrice;
                }
            }
        }
        $this->UpdateMarket();
    }
    //贸易计算//
    function newTrade($start,$targetCountry) {
        $stars = Star::get()->toArray();
        $hubArray = [];
        foreach ($stars as $key => $value) {
            $stars[$key]['hyperlane'] = json_decode($value['hyperlane'], true);
            if ($stars[$key]['stationType'] != '' && $stars[$key]['stationType'] != 'outpost') {
                $isTradeHub = Station::where(["position" => $value['id']])->first()->isTradeHub;
                if ($stars[$key]['owner'] == $targetCountry && $isTradeHub == 1) {
                    $hubArray[] = $value['id'];
                }
            }
        }
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
                if (in_array($hyperLane["to"], $hubArray)) {
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
        var_dump($route);
        $route = array_reverse($route);
    }

    function countTrade() {
    $country = Country::where(["tag"=>$this->owner])->first();
        foreach ($this->trades as $key => $value) {
            $targetMarket = new MarketController($value['target']);
            $target = Country::where(["tag"=>$value['target']])->first();
            if ($value['duration'] > 0) {
                foreach ($value['content'] as $key2 => $value2) {
                    if ($value2 > 0) {
                        $price = $targetMarket->goods[$key2]['price']*$value2;
                        $country->energy -= $price;
                        if ($country->economyType == 1) {
                            $country->storage = json_decode($country->storage, true);
                            $country->storage[$key2] += $value2;
                        }
                    }
                    else {
                        $price = $this->goods[$key2]['price']*$value2;
                        $country->energy += $price;
                        if ($country->economyType == 1) {
                            $country->storage = json_decode($country->storage, true);
                            $country->storage[$key2] -= $value2;
                        }
                    }
                }
                $this->trades[$key]['duration'] -= 1;
            }
            else {
                unset($this->trades[$key]);
                $this->trades = array_values($this->trades);
            }
            $this->UpdateMarket();
        }
    }

}

