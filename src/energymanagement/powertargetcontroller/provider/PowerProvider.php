<?php

namespace robske_110\energymanagement\powertargetcontroller\provider;

abstract class PowerProvider{
	/** @var PowerProviderMode */
	private PowerProviderMode $currentMode;

	/**
	 * Override this method with the actual functionality to adjust the mode and powerStep of your PowerProvider
	 * @param PowerProviderMode $mode
	 */
	public function setMode(PowerProviderMode $mode){
		$this->currentMode = $mode;
	}

	public function getMode(): PowerProviderMode{
		return $this->currentMode;
	}

	/**
	 * @return PowerProviderMode[]
	 */
	abstract public function getCurrentlyPossibleModes(): array;
}