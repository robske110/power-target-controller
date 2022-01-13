<?php
declare(strict_types=1);

namespace robske_110\energymanagement\powertargetcontroller\provider;

use RuntimeException;

abstract class PowerProviderMode{
	protected ?PowerStep $selectedPowerStep;

	//TODO: possibly remove powerStep from constructor
	public function __construct(?PowerStep $powerStep = null){
		$this->selectedPowerStep = null;
	}

	public function getSelectedPowerStep(): ?PowerStep{
		return $this->selectedPowerStep;
	}

	public function setSelectedPowerStep(?PowerStep $powerStep){
		if($powerStep !== null && !in_array($powerStep, $this->getPossiblePowerSteps())){
			throw new RuntimeException("Supplied PowerStep is not a possible PowerStep!");
		}
		$this->selectedPowerStep = $powerStep;
	}

	/**
	 * @return PowerStep[]
	 */
	abstract public function getPossiblePowerSteps(): array;
}