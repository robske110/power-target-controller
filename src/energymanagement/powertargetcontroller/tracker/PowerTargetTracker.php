<?php
declare(strict_types=1);

namespace robske_110\energymanagement\powertargetcontroller\tracker;

use robske_110\energymanagement\powertargetcontroller\provider\PowerProvider;
use robske_110\energymanagement\powertargetcontroller\provider\PowerProviderMode;

//TODO: dropOff functionality -> middleware for pwrStepChg
class PowerTargetTracker{
	private PowerProvider $powerProvider;

	/** @var PowerStepChangeListener[] */
	private array $powerStepChangeListeners = [];
	/** @var PowerProviderModeChangeListener[] */
	private array $powerProviderModeChangeListeners = [];

	public function __construct(PowerProvider $powerProvider){
		$this->powerProvider = $powerProvider;
	}

	public function trackTarget(float $powerTarget){
		//TODO: a ton of logic!

		//Simple nearest algorithm:
		$this->setMode($this->trackMinDiff($powerTarget));
	}

	private function setMode(PowerProviderMode $mode){
		$oldMode = $this->powerProvider->getMode();
		if(get_class($oldMode) === get_class($mode)){
			if($oldMode->selectedPowerStep == $mode->selectedPowerStep){
				return;
			}
			foreach($this->powerStepChangeListeners as $powerStepChangeListener){
				if(!$powerStepChangeListener->onPowerStepChange($oldMode, $mode)){
					return;
				}
			}
		}
		foreach($this->powerProviderModeChangeListeners as $modeChangeListener){
			if(!$modeChangeListener->onPowerModeChange($oldMode, $mode)){
				return;
			}
		}
		$this->powerProvider->setMode($mode);
	}

	private function trackMinDiff(float $powerTarget): PowerProviderMode{
		$minDiff = PHP_FLOAT_MAX;
		$nearestMode = null;
		foreach($this->powerProvider->getCurrentlyPossibleModes() as $powerProviderMode){
			foreach($powerProviderMode->getPossiblePowerSteps() as $possiblePowerStep){
				$currentDiff = min($minDiff, abs($powerTarget - $possiblePowerStep->getPowerValue()));
				if($currentDiff < $minDiff){
					$minDiff = $currentDiff;
					$powerProviderMode->selectedPowerStep = $possiblePowerStep;
					$nearestMode = $powerProviderMode;
				}
			}
		}
		if($nearestMode === null){
			throw new PowerTargetTrackerException(
				"PowerTargetTracker failed to track powerTarget: PowerProvider returned no possible powerSteps!"
			);
		}
		return $nearestMode;
	}
}

