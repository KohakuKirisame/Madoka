<?php

namespace App\Http\Controllers;

class CountryController
{
    var $id,$name,$color,$capital,$energy,$stars,$planets,$financeList,$policyList;
    var $techs,$techList,$ModifierList;

    function __construct($id)
    {
        $this->id = $id;
    }
}
