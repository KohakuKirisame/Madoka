<?php

namespace app\Http\Controllers;
use app\Models\Planet;

class PlanetController extends Controller {

    private $name;
    function __construct($id) {
        $planet=Planet::where(["id"=>$id])->first();
        $this->name = $planet->name;
    }

    function popGrowth(){
        $type = $this->type;
        $result = $conn->query("SELECT * FROM PlanetTypes WHERE name='$type'");
        $typeD = $result->fetch_assoc();
        $carryAble = $typeD['carryAble'] * $this->size;
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
            $result = $conn->query("SELECT * FROM Pops");
            $pops = $result->fetch_all();
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
        $conn->close();
    }
}
