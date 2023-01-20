<?php

namespace App\Http\Controllers;

use app\Models\Population;

class PopController extends Controller {
    public int $id,$position;
    public string $species,$job,$workat,$ethic,$ig,$party;
    public float $cash,$struggle;
    function __construct($id)
    {
        $p = Population::where(["id"=>$id])->first();
        $this->id = $id;
        $this->position = $p->position;
        $this->species = $p->species;
        $this->job = $p->job;
        $this->workat = $p->workat;
        $this->ethic = $p->ethic;
        $this->ig = $p->ig;
        $this->party = $p->party;
        $this->cash = $p->cash;
        $this->struggle = $p->struggle;
    }
    function findJob(){
        if ($this->job == '无' || $this->workat == '无') {
            $planet = new PlanetController($this->position);
            foreach ($planet->districts as $key => $value) {
                $d = readDistrict($key);
                $upJob = 0;
                foreach ($d->job['upJob'] as $key2 => $value2) {
                    $upJob += $value2;
                }
                if (count($value['jobs']['upJob']) < $value['size']*$upJob) {
                    $this->job = 'up';
                    $this->workat = $key;
                    array_push($p->districts[$key]['jobs']['upJob'],$this->id*1);
                    break;
                }
                $midJob = 0;
                foreach ($d->job['midJob'] as $key2 => $value2) {
                    $midJob += $value2;
                }
                if (count($value['jobs']['midJob']) < $value['size']*$midJob) {
                    $this->job = 'mid';
                    $this->workat = $key;
                    array_push($p->districts[$key]['jobs']['midJob'],$this->id*1);
                    break;
                }
                $lowJob = 0;
                foreach ($d->job['lowJob'] as $key2 => $value2) {
                    $lowJob += $value2;
                }
                if (count($value['jobs']['lowJob']) < $value['size']*$lowJob) {
                    $this->job = 'low';
                    $this->workat = $key;
                    array_push($p->districts[$key]['jobs']['lowJob'],$this->id*1);
                    break;
                }
            }
            $p->updatePlanet();
            $id = $this->id;
            $job = $this->job;
            $workat = $this->workat;
            $result = $conn->query("UPDATE Pops SET job='$job',workat='$workat' WHERE id=$id");
        }
        //////////
        else {
            $p = readPlanet($this->position*1);
            if ($p->districts[$this->workat]['cashFlag'] < 0) {
                foreach ($p->districts as $key => $value) {
                    if ($value['cashFlag'] > 0) {
                        $d = readDistrict($key);
                        $upJob = 0;
                        foreach ($d->job['upJob'] as $key2 => $value2) {
                            $upJob += $value2;
                        }
                        if (round($value['size'])*count($value['jobs']['upJob']) < $upJob) {
                            $this->job = 'up';
                            $this->workat = $key;
                            array_push($p->districts[$key]['jobs']['upJob'],$this->id*1);
                            foreach ($p->districts[$this->workat]['jobs'] as $key2 => $value2) {
                                foreach ($value2 as $key3 => $value3) {
                                    if ($value3 == $this->id*1) {
                                        unset($p->districts[$this->workat]['jobs'][$key2][$key3]);
                                        break 2;
                                    }
                                }
                            }
                            break;
                        }
                        $midJob = 0;
                        foreach ($d->job['midJob'] as $key2 => $value2) {
                            $midJob += $value2;
                        }
                        if (round($value['size'])*count($value['jobs']['midJob']) < $midJob && $this->job != 'up') {
                            $this->job = 'mid';
                            $this->workat = $key;
                            array_push($p->districts[$key]['jobs']['midJob'],$this->id*1);
                            foreach ($p->districts[$this->workat]['jobs'] as $key2 => $value2) {
                                foreach ($value2 as $key3 => $value3) {
                                    if ($value3 == $this->id*1) {
                                        unset($p->districts[$this->workat]['jobs'][$key2][$key3]);
                                        break 2;
                                    }
                                }
                            }
                            break;
                        }
                        $lowJob = 0;
                        foreach ($d->job['lowJob'] as $key2 => $value2) {
                            $lowJob += $value2;
                        }
                        if (round($value['size'])*count($p->districts[$key]['jobs']['lowJob']) < $lowJob && $this->job == 'low') {
                            $this->job = 'low';
                            $this->workat = $key;
                            array_push($p->districts[$key]['jobs']['lowJob'],$this->id*1);
                            foreach ($p->districts[$this->workat]['jobs'] as $key2 => $value2) {
                                foreach ($value2 as $key3 => $value3) {
                                    if ($value3 == $this->id*1) {
                                        unset($p->districts[$this->workat]['jobs'][$key2][$key3]);
                                        break 2;
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
            }
            $p->updatePlanet();
            $id = $this->id;
            $job = $this->job;
            $workat = $this->workat;
            $result = $conn->query("UPDATE Pops SET job='$job',workat='$workat' WHERE id=$id");
        }
        $conn->close();
}

