<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\District;
use App\Models\Planet;
use App\Models\Population;
use App\Models\Species;

class PopController extends Controller {
    public int $id,$position;
    public string $species,$job,$class,$workat,$ethic,$ig,$party;
    public float $cash,$struggle;
    function __construct($id) {
        echo $id;
        $p = Population::where(["id"=>$id])->first();
        $this->id = $id;
        $this->position = $p->position;
        $this->species = $p->species;
        $this->job = $p->job;
        $this->class = $p->class;
        $this->workat = $p->workat;
        $this->ethic = $p->ethic;
        $this->ig = $p->ig;
        $this->party = $p->party;
        $this->cash = $p->cash;
        $this->struggle = $p->struggle;
    }
    //自动寻找工作//
    function findJob() {
        $country = Country::get()->toArray();
        $right = 0;
        foreach ($country as $key => $value) {
            $country[$key]['planets'] = json_decode($value['planets']);
            if (in_array($this->position,$country[$key]['planets'])) {
                $species = json_decode($value['species'],true);
                foreach ($species as $specie) {
                    if ($specie['name'] == $this->species) {
                        $right = $specie['right'];
                    }
                    $nationality = $country[$key]['tag'];
                    break;
                }
            }
        }
        if ($this->job == '无' || $this->workat == '无') {
            $p = Planet::where(["id" => $this->position])->first();
            $districts = json_decode($p->districts, true);
            foreach ($districts as $key => $value) {
                $d = District::where(["name" => $value['name']])->first();
                $d->job = json_decode($d->job, true);
                if ($districts[$key]['ownership'] != 2) {
                    $upJob = 0;
                    foreach ($d->job['upJob'] as $key2 => $value2) {
                        $upJob += $value2;
                    }
                    if (count($value['jobs']['upJob']) < $value['size'] * $upJob && $right > 0.67) {
                        if ($value['ownership' == 2]) {
                            continue;
                        }
                        foreach ($d->job['upJob'] as $job => $number) {
                            $this->job = $job;
                            break;
                        }
                        $this->class = 'up';
                        $this->workat = $value['name'];
                        $districts[$key]['jobs']['upJob'][] = $this->id * 1;
                        break;
                    }
                }
                $midJob = 0;
                foreach ($d->job['midJob'] as $key2 => $value2) {
                    $midJob += $value2;
                }
                if (count($value['jobs']['midJob']) < $value['size'] * $midJob && $right>0.33) {
                    foreach ($d->job['midJob'] as $job => $number) {
                        $this->job = $job;
                        break;
                    }
                    $this->class = 'mid';
                    $this->workat = $value['name'];
                    $districts[$key]['jobs']['midJob'][] = $this->id * 1;
                    break;
                }
                $lowJob = 0;
                foreach ($d->job['lowJob'] as $key2 => $value2) {
                    $lowJob += $value2;
                }
                if (count($value['jobs']['lowJob']) < $value['size'] * $lowJob) {
                    if ($right == 0) {
                        $districts[$key]['cash'] -= 500;
                    }
                    foreach ($d->job['lowJob'] as $job => $number) {
                        $this->job = $job;
                        break;
                    }
                    $this->class = 'low';
                    $this->workat = $value['name'];
                    $districts[$key]['jobs']['lowJob'][] = $this->id * 1;
                    break;
                }
            }
        } //////////
        else {
            $p = Planet::where(["id" => $this->position * 1])->first();
            $districts = json_decode($p->districts, true);
            $profitArray = [];
            foreach ($districts as $key => $value) {
                $profitArray= array_merge($profitArray,[$value['name']=>$value]);
            }
            arsort($profitArray);
            foreach ($profitArray as $key => $value) {
                $d = District::where(["name" => $key])->first();
                $jobs = json_decode($d->job, true);
                foreach ($districts as $key2 => $district) {
                    if ($key == $district['name']) {
                        $key = $key2;
                        break;
                    }
                }
                if ($districts[$key]['ownership'] != 2) {
                    $upJob = 0;
                    foreach ($jobs['upJob'] as $key2 => $value2) {
                        $upJob += $value2;
                    }
                    if (round($districts[$key]['size']) * count($districts[$key]['jobs']['upJob']) < $upJob) {
                        foreach ($jobs['upJob'] as $job => $number) {
                            $this->job = $job;
                            break;
                        }
                        $this->class = 'up';
                        $this->workat = $value['name'];
                        $districts[$key]['jobs']['upJob'][] = $this->id * 1;
                        foreach ($districts[$key]['jobs'] as $key2 => $value2) {
                            $key3 = array_search($this->id, $value2);
                            unset($districts[$key]['jobs'][$key2][$key3]);
                            break 2;
                        }
                        break;
                    }
                }
                $midJob = 0;
                 foreach ($jobs['midJob'] as $key2 => $value2) {
                    $midJob += $value2;
                }
                if (round($districts[$key]['size']) * count($districts[$key]['jobs']['midJob']) < $midJob && $this->job != 'up') {
                    foreach ($jobs['midJob'] as $job => $number) {
                        $this->job = $job;
                        break;
                    }
                    $this->class = 'mid';
                    $this->workat = $districts[$key]['name'];
                    $districts[$key]['jobs']['midJob'][] = $this->id * 1;
                    foreach ($districts[$key]['jobs'] as $key2 => $value2) {
                        $key3 = array_search($this->id, $value2);
                        unset($districts[$key]['jobs'][$key2][$key3]);
                        break 2;
                    }
                    break;
                }
                $lowJob = 0;
                foreach ($jobs['lowJob'] as $key2 => $value2) {
                    $lowJob += $value2;
                }
                if (round($districts[$key]['size']) * count($districts[$key]['jobs']['lowJob']) < $lowJob && $this->job == 'low') {
                    foreach ($jobs['lowJob'] as $job => $number) {
                        $this->job = $job;
                        break;
                    }
                    $this->class = 'low';
                    $this->workat = $districts[$key]['name'];
                    $districts[$key]['jobs']['lowJob'][] = $this->id * 1;
                    foreach ($districts[$key]['jobs'] as $key2 => $value2) {
                        $key3 = array_search($this->id, $value2);
                        unset($districts[$key]['jobs'][$key2][$key3]);
                        break 2;
                    }
                    break;
                }
            }
        }
        $p->districts = json_encode($districts, JSON_UNESCAPED_UNICODE);
        Planet::where(["id" => $this->position])->update(["districts" => $districts]);
        Population::where(["id" => $this->id])->update(["job" => $this->job,"class"=>$this->class,"workat" => $this->workat]);
    }
    //投资//
    function invest() {
        $country = Country::get()->toArray();
        foreach ($country as $key => $value) {
            $country[$key]['planets'] = json_decode($value['planets']);
            if (in_array($this->position,$country[$key]['planets'])) {
                $m = new MarketController($country[$key]['tag']);
                $countryKey = $country[$key]['tag'];
                break;
            }
        }
        $line = 6*$m->goods['consume_goods']['price'];
        if ($this->cash >= $line) {
            $cashPool = Country::where(["tag"=>$countryKey])->first()->cashPool;
            $cashPool += 0.5*($this->cash - $line);
            $this->cash -= 0.5*($this->cash - $line);
            Country::where(["tag"=>$countryKey])->update(["cashPool"=>$cashPool]);
        }
    }
    //获取物资//
    function getNeeds() {
        $needs = json_decode(Species::where(["name"=>$this->species])->first()->needs,true);
        $country = Country::get()->toArray();
        $economyType = 0;
        $nationality = '';
        $nationalityKey = '';
        foreach ($country as $key => $value) {
            $country[$key]['planets'] = json_decode($value['planets']);
            if (in_array($this->position,$country[$key]['planets'])) {
                $economyType = $country[$key]['economyType'];
                $m = new MarketController($country[$key]['tag']);
                $nationality = $country[$key]['tag'];
                $nationalityKey = $key;
                break;
            }
        }
        if ($economyType == 0) {
            $planetProduct = json_decode(Planet::where(["id"=>$this->position])->first()->product,true);
            foreach ($needs[$this->class] as $key => $value) {
                $this->cash -= $m->goods[$key]['price']*$value;
                if ($this->cash < 0) {
                    $this->struggle = -$this->cash;
                    $this->cash = 0;
                }
                $planetProduct['market'][$key] -= $value;
            }
            Population::where(["id"=>$this->id])->update(["cash"=>$this->cash,"struggle"=>$this->struggle]);
            $planetProduct = json_encode($planetProduct,JSON_UNESCAPED_UNICODE);
            Planet::where(["id"=>$this->position])->update(["product"=>$planetProduct]);
        }
        else {
            $planetProduct = json_decode(Planet::where(["id"=>$this->position])->first()->product,true);
            $storage = json_decode($country[$nationalityKey]['storage'],true);
            foreach ($needs[$this->class] as $key => $value) {
                $planetProduct['country'][$key] -= $value;
            }
            $planetProduct = json_encode($planetProduct,JSON_UNESCAPED_UNICODE);
            Planet::where(["id"=>$this->position])->update(["product"=>$planetProduct]);
        }
    }
}

