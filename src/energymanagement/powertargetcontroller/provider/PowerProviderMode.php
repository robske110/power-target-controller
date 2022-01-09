<?php

namespace robske_110\energymanagement\powertargetcontroller\provider;

abstract class PowerProviderMode{
	public ?PowerStep $selectedPowerStep;

	//TODO: possibly remove powerStep from constructor
	public function __construct(?PowerStep $powerStep = null){
		$this->selectedPowerStep = null;
	}

	//TODO: maybe check getPossiblePowerSteps on set (protected & introduce set/get)

	/**
	 * @return PowerStep[]
	 */
	abstract public function getPossiblePowerSteps(): array;
}