<?php

namespace App\Http\Controllers;

use App\Models\Situation;

class SituationController extends Controller {
    var $country,$title,$description,$baseProgress,$process;
    public function __construct($id) {
        $s = Situation::where('id',$id)->first();
        $this->country=$s->country;
        $this->title=$s->title;
        $this->description=$s->description;
        $this->baseProgress=$s->baseProgress;
        $this->process=$s->process;
    }

    function countSituation() {
        $this->process += $this->baseProgress;
        Situation::where('id', $this->id)->update(["process" => $this->process]);
    }
}
