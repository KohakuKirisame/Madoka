<?php

namespace App\Http\Controllers;

use App\Models\Country;
use app\Models\Star;
use App\Models\StationType;

class StationController extends Controller
{
    public string $name, $type, $owner, $controller;
    public array $modules, $buildings;
    public int $yardCapacity, $isTradeHub;

    function __construct($position)
    {
        $s = Station::where('position', $position)->first();
        $this->name = $s->name;
        $this->type = $s->type;
        $this->owner = $s->owner;
        $this->controller = $s->controller;
        $this->modules = json_decode($s->modules, true);
        $this->yardCapacity = $s->yardCapacity;
        $this->isTradeHub = $s->isTradeHub;
        $this->buildings = json_decode($s->buildings, true);
    }

    //升级//
    public function rootUpgrade()
    {
        $nextLevel = StationType::where(["type" => $this->type])->first()->nextLevel;
        if ($nextLevel == '') {
            return 0;
        }
        $this->type = $nextLevel;
        Stations::where(["position" => $this->position])->update(["type" => $this->type]);
    }
    public function upgrade()
    {
        if ($this->owner != $this->controller) {
            return 0;
        }
        $nextLevel = StationType::where(["type" => $this->type])->first()->nextLevel;
        if ($nextLevel == '') {
            return 0;
        } else {
            $c = Country::where(["tag" => $this->owner])->first();
            $buildCost = StationType::where(["type" => $this->type])->first()->buildCost;
            $storage = json_decode($c->storage, true);
            if ($storage['alloys'] < $buildCost) {
                return 0;
            } else {
                $storage['alloys'] -= $buildCost;
                $this->type = $nextLevel;
                $storage = json_decode($storage, true);
                Stations::where(["position" => $this->position])->update(["type" => $this->type]);
                Country::where(["tag" => $this->owner])->updare(["storage" => $storage]);
            }
        }
    }
    //降级//
    public function demote(){
        $uponLevel = Stations::where(["nextLevel"=>$this->type])->first()->type;
        $this->type = $uponLevel;
        Stations::where(["position" => $this->position])->update(["type" => $this->type]);
    }
    //拆除//
    public function demolish(){
        if ($this->type == 'outpost') {
            Stations::where(["position" => $this->position])->delete();
            Star::where(["id"=>$this->position])->update(["stationType" => "","owner"=>"","controller"=>""]);
        }
    }
}
