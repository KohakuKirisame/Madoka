<?php

namespace app\Http\Controllers;
use app\Models\Planet;
use app\Models\PlanetType;
use app\Models\Population;

class PlanetController extends Controller {

    private int $id;
    private var $position,$name,$type,$size,$owner,$controller,$leaderParty,$popGrowthProcess;
    private array $pops;
    private array $districts;
    private array $product;


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

    function popGrowth(){
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
}
