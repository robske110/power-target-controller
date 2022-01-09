<?php
declare(strict_types=1);

namespace robske_110\energymanagement\powertargetcontroller\provider;

interface PowerStep{
	/** @return float Wattage of this powerStep */
	function getPowerValue(): float;
}