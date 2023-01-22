<?php

namespace App\Http\Controllers;

use App\Models\Country;
use app\Models\Planet;
use app\Models\Population;

class PopController extends Controller {
    public int $id,$position;
    public string $species,$job,$workat,$ethic,$ig,$party;
    public float $cash,$struggle;
    function __construct($id) {
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
    function findJob() {
        if ($this->job == '无' || $this->workat == '无') {
            $p = Planet::where(["id" => $this->position])->first();
            $p->districts = json_decode($p->districts, true);
            foreach ($p->districts as $key => $value) {
                $d = District::where(["name" => $key])->first;
                $d->job = json_decode($d->job, true);
                if ($p->districts[$key]['ownership'] != 2) {
                    $upJob = 0;
                    foreach ($d->job['upJob'] as $key2 => $value2) {
                        $upJob += $value2;
                    }
                    if (count($value['jobs']['upJob']) < $value['size'] * $upJob) {
                        if ($value['ownership' == 2]) {
                            continue;
                        }
                        $this->job = 'up';
                        $this->workat = $key;
                        $p->districts[$key]['jobs']['upJob'][] = $this->id * 1;
                        break;
                    }
                }
                $midJob = 0;
                foreach ($d->job['midJob'] as $key2 => $value2) {
                    $midJob += $value2;
                }
                if (count($value['jobs']['midJob']) < $value['size'] * $midJob) {
                    $this->job = 'mid';
                    $this->workat = $key;
                    $p->districts[$key]['jobs']['midJob'][] = $this->id * 1;
                    break;
                }
                $lowJob = 0;
                foreach ($d->job['lowJob'] as $key2 => $value2) {
                    $lowJob += $value2;
                }
                if (count($value['jobs']['lowJob']) < $value['size'] * $lowJob) {
                    $this->job = 'low';
                    $this->workat = $key;
                    $p->districts[$key]['jobs']['lowJob'][] = $this->id * 1;
                    break;
                }
            }
        } //////////
        else {
            $p = Planet::where(["id" => $this->position * 1])->first();
            $p->districts = json_decode($p->districts, true);
            $profitArray = [];
            foreach ($p->districts as $key => $value) {
                $profitArray[] = [$key => $value['profit']];
            }
            $profitArray = arsort($profitArray);
            foreach ($profitArray as $key => $value) {
                $d = District::where(["name" => $key])->first();
                $d->job = json_decode($d->job, true);
                if ($p->districts[$key]['ownership'] != 2) {
                    $upJob = 0;
                    foreach ($d->job['upJob'] as $key2 => $value2) {
                        $upJob += $value2;
                    }
                    if (round($value['size']) * count($value['jobs']['upJob']) < $upJob) {
                        $this->job = 'up';
                        $this->workat = $key;
                        $p->districts[$key]['jobs']['upJob'][] = $this->id * 1;
                        foreach ($p->districts[$this->workat]['jobs'] as $key2 => $value2) {
                            $key3 = array_search($this->id, $value2);
                            unset($p->districts[$this->workat]['jobs'][$key2][$key3]);
                            break 2;
                        }
                        break;
                    }
                }
                $midJob = 0;
                foreach ($d->job['midJob'] as $key2 => $value2) {
                    $midJob += $value2;
                }
                if (round($value['size']) * count($value['jobs']['midJob']) < $midJob && $this->job != 'up') {
                    $this->job = 'mid';
                    $this->workat = $key;
                    $p->districts[$key]['jobs']['midJob'][] = $this->id * 1;
                    foreach ($p->districts[$this->workat]['jobs'] as $key2 => $value2) {
                        $key3 = array_search($this->id, $value2);
                        unset($p->districts[$this->workat]['jobs'][$key2][$key3]);
                        break 2;
                    }
                    break;
                }
                $lowJob = 0;
                foreach ($d->job['lowJob'] as $key2 => $value2) {
                    $lowJob += $value2;
                }
                if (round($value['size']) * count($p->districts[$key]['jobs']['lowJob']) < $lowJob && $this->job == 'low') {
                    $this->job = 'low';
                    $this->workat = $key;
                    $p->districts[$key]['jobs']['lowJob'][] = $this->id * 1;
                    foreach ($p->districts[$this->workat]['jobs'] as $key2 => $value2) {
                        $key3 = array_search($this->id, $value2);
                        unset($p->districts[$this->workat]['jobs'][$key2][$key3]);
                        break 2;
                    }
                    break;
                }
            }
        }
        $districts = json_encode($p->districts, JSON_UNESCAPED_UNICODE);
        Planet::where(["id" => $this->position])->update(["districts" => $districts]);
        Population::where(["id" => $this->id])->update(["job" => $this->job, "workat" => $this->workat]);
    }
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
        $line = 6*$m->goods['consume_goods']['prive'];
        if ($this->cash >= $line) {
            $cashPool = Country::where(["tag"=>$countryKey])->first()->cashPool;
            $cashPool += 0.5*($this->cash - $line);
            $this->cash -= 0.5*($this->cash - $line);
            Country::where(["tag"=>$countryKey])->update(["cashPool"=>$cashPool]);
        }
    }
}

