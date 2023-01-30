<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Good;
use App\Models\Market;
use App\Models\Planet;
use App\Models\Star;
use App\Models\User;
use App\Models\Station;
use Illuminate\Http\Request;

class MarketController extends Controller {
    public string $owner;
    public array $member,$planets,$trades,$goods;
    function __construct($country=-1) {
        if($country!=-1) {
            $m = Market::where(["owner" => $country])->first();
            $this->owner = $country;
            if (is_null($m)) {
                $result = Market::get();
                foreach ($result as $item) {
                    $members = json_decode($item->member, true);
                    if (in_array($country, $members)) {
                        $this->owner = $item->owner;
                        break;
                    }
                }
            }
            $m = Market::where(["owner" => $this->owner])->first();
            $this->member = json_decode($m->member, true);
            $this->planets = json_decode($m->planets, true);
            $this->trades = json_decode($m->trades, true);
            $this->goods = json_decode($m->goods, true);
            $goods = Good::get()->toArray();
            $goodsArray = [];
            foreach ($this->goods as $key => $value) {
                $goodsArray[] = $key;
            }
            foreach ($goods as $good) {
                if (!in_array($good['name'], $goodsArray)) {
                    $this->goods = array_merge($this->goods, [$good['name'] => ["demandOrder" => 0, "supplyOrder" => 0, "price" => $good['basePrice'], "storage" => 0]]);
                }
            }
        }
    }
    function UpdateMarket(){
        $member = json_encode($this->member,JSON_UNESCAPED_UNICODE);
        $planets = json_encode($this->planets,JSON_UNESCAPED_UNICODE);
        $trades = json_encode($this->trades,JSON_UNESCAPED_UNICODE);
        $goods = json_encode($this->goods,JSON_UNESCAPED_UNICODE);
        Market::where(["owner"=>$this->owner])->update(["member"=>$member,"planets"=>$planets,"trades"=>$trades,"goods"=>$goods]);
    }
    //价格计算//
    function priceCount() {
        foreach ($this->goods as $key => $value) {
            $this->goods[$key]['supplyOrder'] = 0;
            $this->goods[$key]['demandOrder'] = 0;
        }
        foreach ($this->planets as $key => $value) {
            $product = json_decode(Planet::where(["id"=>$value])->first()->product,true);
            foreach ($this->goods as $key2 => $value2) {
                if ($product['market'][$key2] < 0) {
                    $this->goods[$key2]['demandOrder'] -= $product['market'][$key2];
                }
                else {
                    $this->goods[$key2]['supplyOrder'] += $product['market'][$key2];
                }
            }
        }
        foreach ($this->trades as $key => $value) {
            if ($value['duration'] > 0) {
                foreach ($value['content'] as $key2 => $value2) {
                    if ($value2 > 0 ) {
                        $this->goods[$value2[0]]['supplyOrder'] += $value2;
                    }
                    else {
                        $this->goods[$value2[0]]['demandOrder'] -= $value2;
                    }
                }
            }
        }
        foreach ($this->member as $country) {
            $stars = json_decode(Country::where(["tag"=>$country])->first()->stars,true);
            foreach ($stars as $key => $value) {
                $resource = json_decode(Star::where(["id"=>$value])->first()->resource,true);
                foreach ($resource as $key2 => $value2) {
                    $this->goods[$key2]['supplyOrder'] += $value2;
                }
            }
        }
        $ecoType = Country::where(["tag"=>$this->owner])->first()->economyType;
        if ($ecoType == 0) {
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
        } else {
            $storage = json_decode(Country::where(["tag"=>$this->owner])->first()->storage, true);
            foreach ($this->goods as $key => $value) {
                $storage[$key] += $value['supplyOrder'];
                $storage[$key] -= $value['demandOrder'];
            }
            $storage = json_encode($storage,JSON_UNESCAPED_UNICODE);
            Country::where(["tag"=>$this->owner])->update(["storage"=>$storage]);
        }
        $this->UpdateMarket();
    }
    //贸易计算//
    function newTradeFunc($start,$targetCountry,$resource,$num,$duration) {
        $stars = Star::get()->toArray();
        $hubArray = [];
        foreach ($stars as $key => $value) {
            $stars[$key]['hyperlane'] = json_decode($value['hyperlane'], true);
            if ($stars[$key]['owner'] == $targetCountry && $stars[$key]['isTradeHub'] == 1) {
                $hubArray[] = $value['id'];
            }
        }
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
        $route = array_reverse($route);
        $this->trades[] = ["target"=>$targetCountry,"content"=>[$resource,$num],"path"=>$route,"duration"=>$duration];
        $trade = json_encode($this->trades,JSON_UNESCAPED_UNICODE);
        Market::where(["owner"=>$this->owner])->update(["trades"=>$trade]);
    }
    public function newTrade(Request $request) {
        $id = $request->input('id');
        $target = $request->input('target');
        $resource = $request->input('resource');
        $value = $request->input('value');
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid" => $uid])->first();
        $privilege = $MadokaUser->privilege;
        $country = $MadokaUser->country;
        $id = Country::where(["name" => $id])->first()->tag;
        if ($target == "" || $resource="" || $value="" || is_null($target) || is_null($resource) || is_null($value) || $resource == false) {
            return;
        }
        if ($id == $country) {
            $this->owner = $id;
            $this->newTradeFunc(Country::where(["tag"=>$id])->first()->capital,$target,$resource,$value,99);
            $m = new MarketController($target);
            $m->newTradeFunc(Country::where(["tag"=>$target])->first()->capital,$id,$resource,-$value,99);
        }
    }
    public function deleteTrade(Request $request) {
        $id = $request->input('id');
        $target = $request->input('target');
        $resource = $request->input('resource');
        $value = $request->input('value');
        $uid = $request->session()->get('uid');
        $MadokaUser = User::where(["uid" => $uid])->first();
        $country = $MadokaUser->country;
        $id = Country::where(["name" => $id])->first()->tag;
        $target = Country::where(["name" => $target])->first()->tag;
        if ($id == $country) {
            $this->__construct($id);
            foreach ($this->trades as $key=>$trade) {
                if ($trade["target"] == $target && $trade["content"][0] == $resource && $trade["content"][1] == $value) {
                    array_splice($this->trades,$key,1);
                    break;
                }
            }
            $this->UpdateMarket();
            $m = new MarketController($target);
            foreach ($m->trades as $key=>$trade) {
                if ($trade["target"] == $target && $trade["content"][0] == $resource && $trade["content"][1] == -$value) {
                    array_splice($this->trades,$key,1);
                    break;
                }
            }
            $m->UpdateMarket();
        }
    }
    function countTrade() {
    $country = Country::where(["tag"=>$this->owner])->first()->toArray();
        foreach ($this->trades as $key => $value) {
            $targetMarket = new MarketController($value['target']);
            $target = Country::where(["tag"=>$value['target']])->first();
            if ($value['duration'] > 0) {
                if ($value['content'][1] > 0) {
                    $price = $targetMarket->goods[$value['content'][0]]['price']*$value['content'][1];
                    $country['energy'] -= $price;
                    if ($country['economyType'] == 1) {
                        $country['storage'] = json_decode($country['storage'], true);
                        $country['storage'][$value['content'][0]] += $value['content'][1];
                    }
                }
                else {
                    $price = $this->goods[$value['content'][0]]['price'] * $value['content'][1];
                    $country->energy += $price;
                    if ($country['economyType'] == 1) {
                        $country['storage'] = json_decode($country['storage'], true);
                        $country['storage'][$value['content'][0]] -= $value['content'][1];
                    }
                }
                $this->trades[$key]['duration'] -= 1;
            }
            else {
                unset($this->trades[$key]);
                array_values($this->trades);
            }
            $this->UpdateMarket();
        }
    }
    public function marketPage(Request $request){
        $uid=$request->session()->get('uid');
        $u = User::where(["uid"=>$uid])->first();
        $user=UserController::GetInfo($uid);
        if (key_exists("Err",$user)){
            return redirect("/Action/Logout");
        }
        $privilege = $u->privilege;
        if ($privilege==0){
            $country="GSK";

        }else{
            $country=$u->country;
        }
        $this->__construct($country);
        $this->owner = Country::where(["tag"=>$this->owner])->first()->name;
        foreach ($this->planets as $key=>$planet) {
            $this->planets[$key] = Planet::where(["id"=>$planet])->first()->name;
        }
        foreach ($this->trades as $key=>$trade) {
            $this->trades[$key]['target'] = Country::where(["tag"=>$trade['target']])->first()->name;
            foreach($trade['path'] as $key2=>$star) {
                $this->trades[$key]['path'][$key2] = Star::where(["id"=>$star])->first()->name;
            }
        }
        $markets = Market::get()->toArray();
        foreach ($markets as $key=>$m) {
            if (Country::where(["tag"=>$m['owner']])->first()->economyType == 1) {
                unset($markets[$key]);
                continue;
            }
            if ($m['owner'] == Country::where(["name"=>$this->owner])->first()->tag) {
                unset($markets[$key]);
                continue;
            }
            $markets[$key]['goods'] = json_decode($m['goods'],true);
            $markets[$key]['owner'] = [$m['owner'],Country::where(["tag"=>$m['owner']])->first()->name];
        }
        return view("market",["user"=>$user,"market"=>$this,"markets"=>$markets,"privilege"=>$privilege]);
    }
    public function readMarket(Request $request){
        $id = $request->input("id");
        $goods = Market::where(["owner"=>$id])->first()->goods;
        return $goods;
    }
}

