<?php

namespace App\Models;

class Country extends \Illuminate\Database\Eloquent\Model{
    protected $table = 'country';
    protected $fillable = [
        'name','color','capital','energy','stars','planets','storage','cashPool',
        'atWarWith','alliedWith','financeList','policyList','districtTax',
        'upPopTax','midPopTax','lowPopTax','ethicsM','ethicsAM','ethicsE','ethicsAE','ethicsP','ethicsAP','ethicsX','ethicsAX',
        'energyProduceModifier','mineralsProduceModifier','grainProduceModifier','consumeGoodsProduceModifier','alloysProduceModifier','gasesProduceModifier','motesProduceModifier','crystalsProduceModifier',
        'energyConsumeModifier','mineralsConsumeModifier','grainConsumeModifier','consumeGoodsConsumeModifier','alloysConsumeModifier','gasesConsumeModifier','motesConsumeModifier','crystalsConsumeModifier',
        'shipPDamageModifier','shipEDamageModifier,','shipShieldModifier','shipArmorModifier','shipHullModifier','shipSpeedModifier','shipEvasionModifier','shipDisengageChanceModifier','shipComputerModifier',
        'armyHPModifier','armyDamageModifier','techPSpeedModifier','techSSpeedModifier','techESpeedModifier',
        'popGrowthModifier','popUpkeepModifier','popLivabilityModifier',
        'ModifierList','techs','techList',
        'created_at','updated_at',
    ];

}
