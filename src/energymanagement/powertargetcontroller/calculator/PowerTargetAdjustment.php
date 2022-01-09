<?php

namespace robske_110\energymanagement\powertargetcontroller\calculator;

interface PowerTargetAdjustment{
	//TYPE: POWER_BUDGET (SOFT!)
	//TYPE: POWER_REQUIREMENT
	//TYPE: POWER_LIMITER
	//TYPE: POWER_MIN
	public function getCurrentAdjustmentValue(): float;
}